<?php

declare(strict_types= 1);

namespace Server;

use Server\Request;
use Server\Helpers\FilesHelper;
use Server\Configuration;
use Server\DEFAULT_PAGES_PATH;
use Server\PUBLIC_PAGES_PATH;
use Server\TMP_FILES_PATH;

class Response
{
    protected array $statusCodes = [
		100 => 'Continue',
		101 => 'Switching Protocols',
	
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
	
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
	
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
	
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	];

    protected array $initialHeaders = [
		"HTTP/1.1" => "",
		"Server" => "",
		"Date" => "",
		"Content-Length" => "",
		"Content-Type" => "",
		"Connection" => "",
	];

	protected Request|null $request = null;
	protected string $body = '';

    public function __construct(Request $request, string $body = "", protected int $status = 200)
    {
		$this->request = $request;
		$this->body = $body === "" ? $this->getDefaultBody() : $body;

		$this->handleRequestBody();

		$this->header("HTTP/1.1", $this->status . " " . $this->statusCodes[$this->status]);
		$this->header("Server", "Pure");
		$this->header("Date", gmdate('D d M Y H:i:s T'));
		$this->header("Content-Length", mb_strlen($this->body));
		$this->header("Content-Type", $this->getContentType());
		$this->header("Connection", $this->status >= 400 ? "close" : "keep-alive");
    }

    public function __invoke(): string
    {
        $statusLine = array_shift($this->initialHeaders);
    
        $headers = "HTTP/1.1" . " " . $statusLine . "\r\n";

        foreach ($this->initialHeaders as $key => $value) {
			if ($key && $value) {
				$headers .= $key .": ". $value ."\r\n";
			}
        }

        $headers .= "\r\n";

        return $headers . $this->body;
    }

	protected function parseRequestBody(string $body): string|bool
	{
		$contentType = $this->request->header("Content-Type");
		$boundry = "";

		if (preg_match("/boundary=(.*)$/", $contentType["arg"], $matches)) {
			$boundry = trim($matches[1]);
		}

		if (!$boundry) return false;

		$topBoundry = "--" . $boundry . "\r";
		$bottomBoundry = "--" . $boundry . "--" . "\r";

		if (!str_starts_with($body, $topBoundry) && str_starts_with($body, $topBoundry . "\n")) {
			$topBoundry .= "\n";
		}

		if (!str_ends_with($body, $bottomBoundry) && str_ends_with($body, $bottomBoundry . "\n")) {
			$bottomBoundry .= "\n";
		}

		if (
			!str_starts_with($body, $topBoundry) || 
			!str_ends_with($body, $bottomBoundry)
		) {
			return false;
		}

		$body = substr($body, strlen($topBoundry) - 1);
		$body = substr($body, 0, -1 * strlen($bottomBoundry));

		preg_match_all("/\r\n/", $body, $bodyBreakers, PREG_OFFSET_CAPTURE);

		if (!$bodyBreakers || count($bodyBreakers) < 1) {
			var_dump(12);
			return false;
		} 

		$finds = count($bodyBreakers[0]);
		if ($finds < 6 || $bodyBreakers[0][$finds - 3] < 2) {
			var_dump(13);
			return false;
		}
		
		$body = substr($body, $bodyBreakers[0][$finds - 3][1], strlen($body));

		return trim($body);
	}

	protected function handleRequestBody(): void
	{
		if ($this->request->getBody() === "" && mb_strlen($this->request->getBody()) === 0) {
			return;
		}

		if ($this->request->method === "POST") {
			$parsedBody = $this->parseRequestBody($this->request->getBody());
			var_dump($parsedBody);

			if (!$parsedBody) {
				return;
			}

			$allowedFileFormats = Configuration::getUserAllowedFileFormats();
			$filename = TMP_FILES_PATH . "\\" . FilesHelper::generateRandomFileName();

			while (file_exists($filename)) {
				$filename = TMP_FILES_PATH . "\\" . FilesHelper::generateRandomFileName();
			}

			file_put_contents($filename, $parsedBody);

			$info = new \finfo(FILEINFO_MIME_TYPE);
			$type = $info->file($filename);

			if (!in_array($type, $allowedFileFormats)) {
				var_dump($type, $allowedFileFormats);
				$this->status = 415;
				unlink($filename);

				return;
			}

			if (str_starts_with($type, "image/")) {
				if (!getimagesize($filename) || !exif_imagetype($filename)) {
					$this->status = 415;
					unlink($filename);
					var_dump(16);

					return;
				}
			}

			// writing to db using orm of some sorts
		}
	}

	protected function header(string $key, mixed $value): void
	{
		$this->initialHeaders[$key] = $value;
	}

	protected function handleAccept(string $accept, string $uri): string 
	{
		$source = PUBLIC_PAGES_PATH . $uri;

		if ($uri === "/") {
			$source = __DIR__ . Configuration::getDefaultPagePath();
		}

		if (str_ends_with($source, "/")) {
			if (file_exists($source)) {
				$source .= "index.html"; 
			} else {
				$source = substr($source, 0, -1);
			}
		}
			
		$sourceInfo = pathinfo($source);

		if (!array_key_exists("extension", $sourceInfo)) {
			// If there is no file extension then find a similar path, with file extension (that exists) and replace it
			$source = FilesHelper::findFileByWildcard($source);
		}

		if (is_file($source) && file_exists($source)) {
			$info = new \finfo(FILEINFO_MIME_TYPE);
			$type = $info->file($source);

			if ($type === $accept) {
				$this->header("Content-Type", $accept);

				return file_get_contents($source);
			} else {
				$this->status = 406;
				$this->header("Content-Type", "text/html");

				return file_get_contents(DEFAULT_PAGES_PATH . "\RequestExceptions\NotAcceptablePage.html");
			}
		}

		$this->status = 404;
		$this->header("Content-Type", "text/html");

		return file_get_contents(DEFAULT_PAGES_PATH . "\RequestExceptions\NotFoundPage.html");
	}

	protected function getDefaultBody(): string
	{
		$acceptString = $this->request->header("Accept", true) ?? "";
		$accept = explode(",", $acceptString);

		if (
			!$accept || !count($accept) ||
			$accept[0] === "text/html" || $accept[0] === "application/json"
		) {
			return $this->handleAccept($accept[0], $this->request->uri);
		} else if (
			str_starts_with(strtolower($accept[0]),"image/") && 
			$this->request->uri === "/favicon.ico"
		) {
			$this->header("Content-Type", "image/png");
			
			return file_get_contents(__DIR__ . Configuration::getIconPath());
		} else {
			$this->status = 406;
			$this->header("Content-Type", "text/html");

			return file_get_contents(DEFAULT_PAGES_PATH . "\RequestExceptions\NotAcceptablePage.html");
		}
	}

	protected function getContentType(): string
	{
		if ($this->initialHeaders["Content-Type"]) {
			return $this->initialHeaders["Content-Type"];
		}

		$info = new \finfo(FILEINFO_MIME_TYPE);
		$type = $info->buffer($this->body);
		
		if ($type) {
			return $type;
		}

		return "text/html";
	}
}