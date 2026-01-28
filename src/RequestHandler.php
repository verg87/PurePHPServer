<?php

declare(strict_types= 1);

namespace Server;

class RequestHandler
{
    private function parse(string $request): array
    {
        $headers = array_filter(explode("\r\n", $request), fn($str) => strlen($str) > 0);
        var_dump($headers);

        $method = $headers[0] ?? "";
        $host = preg_match("/[\d|\.]+/", $headers[1]);

        echo $host;

        return [];
    }

    public function validate(string $request): bool
    {
        $this->parse($request);
        return true;
    }
}