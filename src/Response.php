<?php

declare(strict_types= 1);

namespace Server;

use Server\Request;
use Server\DEFAULT_PAGES_PATH;
use Server\PUBLIC_PAGES_PATH;
use Server\ICONS_PATH;

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
	
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		408 => 'Request Timeout',
	
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

		$this->header("HTTP/1.1", $this->status . " " . $this->statusCodes[$this->status]);
		$this->header("Server", "Pure");
		$this->header("Date", gmdate('D d M Y H:i:s T'));
		$this->header("Content-Length", strlen($this->body));
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

	protected function header(string $key, mixed $value): void
	{
		$this->initialHeaders[$key] = $value;
	}

	protected function getDefaultBody(): string
	{
		$accept = explode(",", $this->request->headers["Accept"] ?? "");
		var_dump(unpack("C*", "A"));

		if (!$accept || !count($accept) || $accept[0] === "text/html") {
			$this->header("Content-Type", "text/html");

			if ($this->request->uri === "/") {
				return file_get_contents(DEFAULT_PAGES_PATH . "\home.html");
			}

			$fileExtension = "";

			if (!str_contains($this->request->uri, ".html")) {
				$fileExtension = ".html";
			}

			if (file_exists(PUBLIC_PAGES_PATH . $this->request->uri . $fileExtension)) {
				return file_get_contents(PUBLIC_PAGES_PATH . $this->request->uri . $fileExtension);
			}

			return file_get_contents(DEFAULT_PAGES_PATH . "\RequestExceptions\NotFoundPage.html");
		} else if (
			str_starts_with(strtolower($accept[0]),"image/") && 
			$this->request->uri === "/favicon.ico"
		) {
			$this->header("Content-Type", "image/png");
			
			return file_get_contents(ICONS_PATH . "\pure-32.png");
		} else {
			$this->status = 501;
			$this->header("Content-Type", "text/html");

			return file_get_contents(DEFAULT_PAGES_PATH . "\RequestExceptions\NotImplementedPage.html");
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