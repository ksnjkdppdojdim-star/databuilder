<?php

namespace DataBuilder\Router;

/**
 * Router simple mais flexible.
 *
 * Supporte :
 *  - Routes statiques  : /user/login
 *  - Routes dynamiques : /user/{id}/profile  → params['id']
 *
 * Sources de routes :
 *  1. config/routes.xml  (déclaratives)
 *  2. Router::add()      (programmatiques, pour les modules)
 */
class Router implements RouterInterface
{
    /** @var array<array{method: string, path: string, regex: string, params: string[], controller: string, action: string, handle: string}> */
    private array $routes = [];
    private string $configPath;

    public function __construct(array $config)
    {
        $this->configPath = $config['config_path'];
        $this->loadFromXml();
    }

    // ─── Matching ─────────────────────────────────────────────────────────────

    /**
     * Tente de matcher une URI contre les routes enregistrées.
     * Retourne la Route résolue ou null si aucune correspondance.
     */
    public function match(string $uri): ?Route
    {
        $uri    = '/' . trim(parse_url($uri, PHP_URL_PATH), '/');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->routes as $route) {
            if (strtoupper($route['method']) !== strtoupper($method)) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $params = [];
                foreach ($route['params'] as $name) {
                    $params[$name] = $matches[$name] ?? null;
                }

                return new Route(
                    $route['method'],
                    $route['path'],
                    $route['controller'],
                    $route['action'],
                    $route['handle'],
                    $params
                );
            }
        }

        return null;
    }

    // ─── Enregistrement ───────────────────────────────────────────────────────

    /**
     * Ajoute une route programmatiquement.
     * Appelable par les modules pour enregistrer leurs propres routes.
     */
    public function add(
        string $method,
        string $path,
        string $controller,
        string $action,
        string $handle = 'default'
    ): void {
        [$regex, $params] = $this->compilePath($path);

        $this->routes[] = compact('method', 'path', 'regex', 'params', 'controller', 'action', 'handle');
    }

    // ─── Chargement XML ───────────────────────────────────────────────────────

    /**
     * Charge les routes depuis config/routes.xml
     *
     * Format attendu :
     * <routes>
     *     <route method="GET" path="/" controller="DataBuilder\Controller\HomeController" action="index" handle="homepage" />
     *     <route method="GET" path="/user/{id}" controller="DataBuilder\Controller\UserController" action="show" handle="user_show" />
     * </routes>
     */
    private function loadFromXml(): void
    {
        $file = $this->configPath . '/routes.xml';

        if (!file_exists($file)) return;

        $xml = simplexml_load_file($file);
        if ($xml === false) {
            throw new \RuntimeException("Failed to parse routes.xml");
        }

        foreach ($xml->route as $route) {
            $this->add(
                (string)($route['method']     ?? 'GET'),
                (string)($route['path']       ?? '/'),
                (string)($route['controller'] ?? ''),
                (string)($route['action']     ?? 'index'),
                (string)($route['handle']     ?? 'default'),
            );
        }
    }

    // ─── Compilation de path ──────────────────────────────────────────────────

    /**
     * Transforme un path avec placeholders en regex.
     * /user/{id}/profile  →  #^/user/(?P<id>[^/]+)/profile$#
     *
     * @return array{0: string, 1: string[]}  [regex, paramNames]
     */
    private function compilePath(string $path): array
    {
        $params = [];

        $regex = preg_replace_callback(
            '/\{(\w+)\}/',
            function (array $matches) use (&$params): string {
                $params[] = $matches[1];
                return '(?P<' . $matches[1] . '>[^/]+)';
            },
            $path
        );

        $regex = '#^' . $regex . '$#';

        return [$regex, $params];
    }
}