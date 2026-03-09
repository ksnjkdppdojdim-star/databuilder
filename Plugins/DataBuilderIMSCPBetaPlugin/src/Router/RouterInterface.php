<?php

namespace DataBuilder\Router;

interface RouterInterface
{
    public function match(string $uri): ?Route;
    public function add(string $method, string $path, string $controller, string $action, string $handle = 'default'): void;
}