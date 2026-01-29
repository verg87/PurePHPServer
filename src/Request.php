<?php

declare(strict_types= 1);

namespace Server;

class Request
{
    public int $error;

    public function __construct(
        public readonly string $method, 
        public readonly string $http, 
        public readonly array $headers
    )
    {
        $this->validate($method, $http, $headers);
    }

    public static function fromHeader(string $request): static
    {
        $lines = array_filter(explode("\r\n", $request), fn($str) => strlen($str) > 0);
        list($method, $http) = explode(" ", array_shift($lines));

        $headers = [];

        foreach ($lines as $line) {
            list($key, $value) = explode(": ", $line);

            $headers[$key] = $value;
        }

        return new static($method, $http, $headers);
    }

    private function validate(string $method, string $http, array $headers): void
    {
        if ($method !== "GET" || $method !== "POST" || $http !== "/ HTTP/1.1") {
            $this->error = 400;
        }

        foreach ($headers as $key => $value) {
            if (!$key || !$value) {
                $this->error = 400;
            }
        }
    }
}