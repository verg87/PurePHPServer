<?php

declare(strict_types= 1);

namespace Server;

use Server\Request;

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

    protected array $initialHeaders = [];

    public function __construct(protected string $body, protected int $status = 200)
    {
        $this->initialHeaders["HTTP/1.1"] = $this->status . " ". $this->statusCodes[$this->status];
        $this->initialHeaders["Date"] = gmdate('D d M Y H:i:s T');
        $this->initialHeaders["Content-Length"] = mb_strlen($body);
        $this->initialHeaders["Connection"] = "keep-alive";
    }

    public function __invoke(string $contentType): string
    {
        $statusLine = array_shift($this->initialHeaders);
    
        $headers = "HTTP/1.1" . " " . $statusLine . "\r\n";

        foreach ($this->initialHeaders as $key => $value) {
            $headers .= $key .": ". $value ."\r\n";
        }

        $headers .= "Content-Type: " . $contentType;
        $headers .= "\r\n\r\n";

        return $headers . $this->body;
    }
}