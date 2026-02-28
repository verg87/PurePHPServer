<?php

require_once __DIR__ . "\\vendor\\autoload.php";

use Server\MVC\Controller;
use Server\Server;
use Server\Router;

$router = (new Router())
    ->get("/", [Controller::class, "index"]);

$server = new Server("127.0.0.1", 80, $router);

$server->listen();