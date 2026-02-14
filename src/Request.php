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
        public readonly string $body,
    )
    {
        $this->validate($method, $http, $headers);
    }

    public static function fromHeader(string $request): static
    {
        var_dump($request);
        $lines = array_filter(explode("\n", $request), fn($str) => strlen($str) > 0);
        list($method, $uri, $http) = explode(" ", array_shift($lines));

        $headers = [];

        $body = "";
        $isBody = false;

        foreach ($lines as $line) {
            if ($isBody) {
                $body .= $line;
                continue;
            }

            if (!str_contains($line, ":") && $line === "\r") {
                $isBody = true;
                continue;
            }

            list($key, $value) = explode(": ", $line);

            $headers[$key] = $value;
        }

        return new static($method, $uri, $http, $headers, $body);
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