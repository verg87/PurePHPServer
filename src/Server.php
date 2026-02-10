<?php

declare(strict_types=1);

namespace Server;

require_once __DIR__ . "/../vendor/autoload.php";

CONST DEFAULT_PAGES_PATH = __DIR__ ."\pages";
CONST PUBLIC_PAGES_PATH = __DIR__ . "\..\public";
CONST ICONS_PATH = __DIR__ . "\..\icons";

class Server
{
    CONST READ_LENGTH = 1024;
    private \Socket $socket;

    public function __construct(
        public readonly string $address, 
        public readonly int $port
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

            $request = Request::fromHeader(socket_read($client, self::READ_LENGTH));
            // var_dump($request->headers);
            $response = new Response($request);

            socket_write($client, $response());

            socket_close($client);
        }
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }
}

$server = new Server("127.0.0.1", 80);

$server->listen();