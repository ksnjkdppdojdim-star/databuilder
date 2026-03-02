<?php

namespace DataBuilder\Core;

use DataBuilder\Core\Config;
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Template\TemplateEngine;
use DataBuilder\Router\Router;
use DataBuilder\Controller\FrontController;

use DataBuilder\Theme\ThemeManager;
use DataBuilder\Cache\CacheManager;
use DataBuilder\Event\EventDispatcher;


class Engine
{
    private static ?Engine $instance = null;
    private array $config = [];
    private Config $config_obj;
    private Registry $registry;
    private LayoutManager $layoutManager;
    private TemplateEngine $templateEngine;
    private Router $router;

    private ThemeManager    $themeManager;
    private CacheManager    $cacheManager;
    private EventDispatcher $eventDispatcher;

    private function __construct(array $config)
    {
        $this->registry   = new Registry();
        $this->config_obj = new Config($config['config_path'] ?? dirname(__DIR__, 2) . '/config');
        $this->config_obj->load($config); // charge XML + merge runtime
        $this->config     = $this->config_obj->all();
        $this->boot();
    }

    public static function create(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Engine not initialized. Call Engine::create() first.');
        }
        return self::$instance;
    }

    private function boot(): void
    {
        // Paths de base
        $basePath = $this->config['base_path'] ?? dirname(__DIR__, 2);

        // ✅ Correct : la config chargée a priorité sur les defaults
        $this->config = array_merge([
            'base_path'     => $basePath,
            'themes_path'   => $basePath . '/themes',
            'modules_path'  => $basePath . '/modules',
            'cache_path'    => $basePath . '/cache',
            'config_path'   => $basePath . '/config',
            'theme'         => 'base',
            'cache_enable'  => true,
            'debug'         => false,
        ], $this->config); // $this->config en second = il écrase les defaults ✅

        // Boot des composants core
        $this->layoutManager  = new LayoutManager($this->config, $this->registry);
        $this->templateEngine = new TemplateEngine($this->config);
        $this->router         = new Router($this->config);

        $this->themeManager    = new ThemeManager($this->config);
        $this->cacheManager    = new CacheManager($this->config);
        $this->eventDispatcher = new EventDispatcher();
        
    }
    
    // Getters publics
    public function getThemeManager(): ThemeManager       { return $this->themeManager; }
    public function getCacheManager(): CacheManager       { return $this->cacheManager; }
    public function getEventDispatcher(): EventDispatcher { return $this->eventDispatcher; }
    

    public function dispatch(): void
    {
        $frontController = new FrontController(
            $this->router,
            $this->layoutManager,
            $this->templateEngine,
            $this->registry
        );

        $frontController->dispatch($_SERVER['REQUEST_URI'] ?? '/');
    }

    public function getConfig(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->config;
        return $this->config_obj->get($key, $default);
    }

    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    public function getLayoutManager(): LayoutManager
    {
        return $this->layoutManager;
    }

    public function getTemplateEngine(): TemplateEngine
    {
        return $this->templateEngine;
    }
}