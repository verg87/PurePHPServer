<?php

declare(strict_types=1);

namespace Server;

use Server\Request;

class Server
{
    CONST READ_LENGTH = 1024;
    private \Socket $socket;

    public function __construct(public readonly string $address, public readonly int $port) 
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

            $source = socket_read($client, self::READ_LENGTH);

            if (!Request::validate($source)) {
                socket_close($client);
                continue;
            }

            socket_write($client, "Hello from my server");

            if (!$source) {
                socket_close($client);
                continue;
            }

            var_dump(strlen($source));
            var_dump($source);

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