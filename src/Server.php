<?php

declare(strict_types=1);

namespace Server;

require_once __DIR__ . "/../vendor/autoload.php";

CONST DEFAULT_PAGES_PATH = __DIR__ ."\pages";
CONST PUBLIC_PAGES_PATH = __DIR__ . "\..\public";
CONST ICONS_PATH = __DIR__ . "\..\icons";
CONST TMP_FILES_PATH = __DIR__ . "\\tmp";

class Server
{
    CONST READ_LENGTH = 1024 * 32;
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

            $headers = socket_read($client, $this::READ_LENGTH);
            var_dump($headers);
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
                // var_dump(2);
                $request->addBody($chunk);

                if ($readAmount === 0);
                    break;
            }

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