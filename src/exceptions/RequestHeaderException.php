<?php

declare(strict_types= 1);

namespace Server\Exceptions;

class RequestHeaderException extends \Exception
{
    protected $message = "Invalid header string";
}