<?php

declare(strict_types= 1);

namespace Server;

class Request
{
    public int $error;

    public function __construct(
        public readonly string $method, 
        public readonly string $uri,
        public readonly string $http, 
        public readonly array $headers,
        protected string $body,
    )
    {
        $this->validate($method, $http, $headers);
    }

    public function addBody(string $body): void
    {
        $this->body .= $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function header(string $name, bool $onlyValue = false): mixed
    {
        $header = array_key_exists($name, $this->headers) ? $this->headers[$name] : false;

        if ($header && $onlyValue) {
            return $header["value"];
        }

        return $header;
    }

    public static function fromHeader(string $headers): static
    {
        // var_dump($headers);
        $lines = array_filter(explode("\n", $headers), fn($str) => strlen($str) > 0);
        list($method, $uri, $http) = explode(" ", array_shift($lines));

        $headersArr = [];

        $body = "";

        foreach ($lines as $index => $line) {
            if (!str_contains($line, ":") && $line === "\r" && $index + 1 !== count($lines)) {
                //?
                $lines = array_map(fn($line) => trim($line), array_slice($lines, $index, count($lines)));
                $body = trim(implode("\r\n", $lines)) . "\r\n";
                break;
            }

            if (trim($line) === "") {
                continue;
            }

            list($header, $headerValue) = explode(": ", $line);
            
            if (str_contains($headerValue, ";")) {
                list($value, $arg) = explode(";", $headerValue);

                $headersArr[$header] = ["value" => $value, "arg" => $arg];
            } else {
                $headersArr[$header] = ["value" => $headerValue, "arg" => ""];
            }
        }

        return new static($method, $uri, $http, $headersArr, $body);
    }

    private function validate(string $method, string $http, array $headers): void
    {
        if ($method !== "GET" || $method !== "POST" || $http !== "HTTP/1.1") {
            $this->error = 400;
        }

        foreach ($headers as $key => $value) {
            if (!$key || !$value) {
                $this->error = 400;
            }
        }
    }
}