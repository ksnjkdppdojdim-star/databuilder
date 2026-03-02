<?php

namespace DataBuilder\Router;

/**
 * Représente une route résolue.
 *
 * Exemple XML :
 * <route method="GET" path="/user/login" controller="DataBuilder\Controller\UserController" action="login" handle="user_login" />
 */
class Route
{
    public function __construct(
        private string $method,
        private string $path,
        private string $controller,
        private string $action,
        private string $handle,       // Layout handle associé (ex: "user_login")
        private array  $params = []   // Paramètres dynamiques extraits de l'URL
    ) {}

    public function getMethod(): string     { return $this->method; }
    public function getPath(): string       { return $this->path; }
    public function getController(): string { return $this->controller; }
    public function getAction(): string     { return $this->action; }
    public function getHandle(): string     { return $this->handle; }
    public function getParams(): array      { return $this->params; }

    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }
}