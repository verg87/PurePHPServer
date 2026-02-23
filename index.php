<?php

require_once __DIR__ . "\\vendor\\autoload.php";

use Server\Server;

$server = new Server("127.0.0.1", 80);

$server->listen();