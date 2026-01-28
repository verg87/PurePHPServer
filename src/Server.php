<?php

declare(strict_types=1);

namespace Server;

require_once __DIR__ . "/../vendor/autoload.php";

class Server
{
    CONST READ_LENGTH = 1024;
    private \Socket $socket;

    public function __construct(
        public readonly string $address, 
        public readonly int $port, 
        private RequestHandler $requestHandler
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

            $source = socket_read($client, self::READ_LENGTH);

            if (!$this->requestHandler->validate($source)) {
                socket_close($client);
                continue;
            }

            socket_write($client, "Hello from my server");

            if (!$source) {
                socket_close($client);
                continue;
            }

            socket_close($client);
        }
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }
}

$requestHandler = new RequestHandler();
$server = new Server("127.0.0.1", 80, $requestHandler);

$server->listen();