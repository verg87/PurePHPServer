<?php

declare(strict_types=1);

namespace Server\MVC;

interface ViewInterface
{
    public static function create(string $name = "", array $params = []): static;
    public function render(): string;
    public function __toString(): string;
}