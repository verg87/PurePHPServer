<?php

declare(strict_types=1);

namespace Server;

use Server\Configuration;

class Router
{
    private array $routes = [];

    public function add(string $method, string $route, callable|array $action): self
    {
        $this->routes[$method][$route] = $action;

        return $this;
    }

    public function get(string $route, callable|array $action): self
    {
        return $this->add('get', $route, $action);
    }

    public function post(string $route, callable|array $action): self
    {
        return $this->add('post', $route, $action);
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function resolve(string $requestUri, string $requestMethod): string
    {
        $route = explode('?', $requestUri)[0];
        $action = $this->routes[strtolower($requestMethod)][$route] ?? null;

        if (!$action) {
            return file_get_contents(Configuration::DEFAULT_PAGES_PATH . "\RequestExceptions\NotFoundPage.html");
        }

        if (is_callable($action)) {
            return call_user_func($action);
        }

        [$class, $method] = $action;

        if (class_exists($class)) {
            $class = new $class();

            if (method_exists($class, $method)) {
                return "" . call_user_func_array([$class, $method], []);
            }
        }

        return file_get_contents(Configuration::DEFAULT_PAGES_PATH . "\RequestExceptions\NotFoundPage.html");
    }
}