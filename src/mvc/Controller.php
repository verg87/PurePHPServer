<?php

declare(strict_types=1);

namespace Server\MVC;

use Server\MVC\ViewInterface;
use Server\MVC\View;

class Controller 
{
    public function index(): ViewInterface
    {
        return View::create();
    }
}