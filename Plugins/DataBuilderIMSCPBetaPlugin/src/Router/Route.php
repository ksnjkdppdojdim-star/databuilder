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
    private $method;
    private $path;
    private $controller;
    private $action;
    private $handle;
    private $params;

    public function __construct(
        $method,
        $path,
        $controller,
        $action,
        $handle,
        $params = []
    ) {
        $this->method     = $method;
        $this->path       = $path;
        $this->controller = $controller;
        $this->action     = $action;
        $this->handle     = $handle;
        $this->params     = $params;
    }

    public function getMethod(): string     { return $this->method; }
    public function getPath(): string       { return $this->path; }
    public function getController(): string { return $this->controller; }
    public function getAction(): string     { return $this->action; }
    public function getHandle(): string     { return $this->handle; }
    public function getParams(): array      { return $this->params; }

    public function getParam(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
}