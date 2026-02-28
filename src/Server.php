<?php

declare(strict_types=1);

namespace Server;

require_once __DIR__ . "\\..\\vendor\\autoload.php";

class Server
{
    // half a MB
    CONST READ_LENGTH = 1024 * 512;
    private \Socket $socket;

    public function __construct(
        public readonly string $address, 
        public readonly int $port,
        private Router|null $router = null,
    ) 
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_bind($this->socket, $address, $port);
    }

    public function listen() : never
    {
        while (true)
        {
            socket_listen($this->socket);

            $client = socket_accept($this->socket);
            
            if (!$client) {
                socket_close($client);
                continue;
            }

            $headers = socket_read($client, $this::READ_LENGTH);
            $request = Request::fromHeader($headers);

            $readAmount = $request->header("Content-Length", true) ?? 0;

            while (
                $request->method === "POST" && 
                str_ends_with($headers, "\r\n\r\n") && 
                $readAmount > 0
            ) 
            {
                $chunk = socket_read($client, $this::READ_LENGTH);
                $readAmount -= mb_strlen($chunk);
                $request->addBody($chunk);

                if ($readAmount === 0);
                    break;
            }

            $body = $this->router 
                ? $this->router->resolve($request->uri, $request->method)
                : "";

            socket_write($client, (new Response($request, 200, $body))());

            socket_close($client);
        }
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }
}