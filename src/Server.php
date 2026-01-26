<?php

declare(strict_types=1);

namespace Server;

class Server
{
    private \Socket $socket;

    public function __construct(public readonly string $address, public readonly int $port) 
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_bind($this->socket, $address, $port);
    }

    public function listen() : void
    {
        socket_listen($this->socket);
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }
}