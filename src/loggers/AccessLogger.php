<?php

declare(strict_types=1);

namespace Server\Loggers;

use Server\Request;
use Server\Response;

class AccessLogger 
{
    public function __construct(private Request $request) 
    {
    }

    static function start(Request $request, Response $response, \Socket $client): void
    {
        $address = "";
        $port = 0;
        socket_getpeername($client, $address, $port);
        
        $info = [
            ":date" => date("D M j G:i:s Y"),
            ":address" => $address,
            ":port" => $port,
            ":status" => $response->status,
            ":method" => $request->method,
            ":uri" => $request->uri,
        ];

        $log = strtr("[:date] [:address]::port [:status] :method :uri", $info);

        echo $log . "\n";
    }
}