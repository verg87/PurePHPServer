<?php

declare(strict_types=1);

namespace Server\MVC;

use Server\MVC\ViewInterface;
use Server\Configuration;

class View implements ViewInterface
{
    public function __construct(
        protected string $name,
        protected array $params = []
    ) {
    }

    public static function create(string $name = "", array $params = []): static
    {
        return new static($name, $params);
    }

    public function render(): string
    {
        $page = Configuration::PUBLIC_PAGES_PATH . '/' . $this->name . '.html';

        if (!$this->name) {
            return file_get_contents(Configuration::DEFAULT_PAGES_PATH . "\index.html");
        }

        if (!file_exists($page)) {
            return file_get_contents(Configuration::DEFAULT_PAGES_PATH . "\RequestExceptions\NotFoundPage.html");
        }

        return file_get_contents($page);
    }

    public function __toString(): string
    {
        return $this->render();
    }
}