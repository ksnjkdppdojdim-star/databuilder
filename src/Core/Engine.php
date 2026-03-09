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

use DataBuilder\Tenant\TenantManager;
use DataBuilder\Module\ModuleLoader;


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

    private TenantManager $tenantManager;
    private ModuleLoader  $moduleLoader;

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
        $basePath = $this->config['base_path'] ?? dirname(__DIR__, 2);
    
        $this->config = array_merge([
            'base_path'     => $basePath,
            'themes_path'   => $basePath . '/themes',
            'modules_path'  => $basePath . '/modules',
            'cache_path'    => $basePath . '/cache',
            'config_path'   => $basePath . '/config',
            'theme'         => 'base',
            'cache_enable'  => true,
            'debug'         => false,
        ], $this->config);
    
        // ✅ Résolution forcée des paths relatifs (fix Windows + Linux)
        foreach (['themes_path', 'modules_path', 'cache_path', 'config_path'] as $key) {
            $value = $this->config[$key];
            if (!str_starts_with($value, '/') && !str_contains($value, ':')) {
                $this->config[$key] = $this->config['base_path'] . DIRECTORY_SEPARATOR . ltrim($value, '/\\');
            } else {
                // ✅ Normalise les séparateurs même pour les paths déjà absolus
                $this->config[$key] = str_replace('/', DIRECTORY_SEPARATOR, $value);
            }
        }
    
        // Boot des composants core
        $this->layoutManager  = new LayoutManager($this->config, $this->registry);
        $this->templateEngine = new TemplateEngine($this->config);
        $this->router         = new Router($this->config);
    
        $this->themeManager    = new ThemeManager($this->config);
        $this->cacheManager    = new CacheManager($this->config);
        $this->eventDispatcher = new EventDispatcher();
    
        $this->tenantManager = new TenantManager($this->config);
        $this->moduleLoader  = new ModuleLoader($this->config);
        $this->moduleLoader->load();
    
        // Si tenant actif → override du thème
        if ($this->tenantManager->isActive()) {
            $this->config['theme'] = $this->tenantManager->resolveTheme($this->config['theme']);
        }
    }
    
    // Getters publics
    public function getThemeManager(): ThemeManager       { return $this->themeManager; }
    public function getCacheManager(): CacheManager       { return $this->cacheManager; }
    public function getEventDispatcher(): EventDispatcher { return $this->eventDispatcher; }

    public function getTenantManager(): TenantManager { return $this->tenantManager; }
    public function getModuleLoader(): ModuleLoader   { return $this->moduleLoader; }
    

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
    
    /**
     * Render a page with the specified layout
     * 
     * This is the main entry point for rendering pages. It:
     * 1. Loads the layout XML file
     * 2. Builds the block hierarchy via BlockFactory
     * 3. Renders all blocks through TemplateEngine
     * 4. Returns the final HTML output
     * 
     * @param string $area The area (admin, client, reseller)
     * @param string $page The page name (e.g., 'index')
     * @param array $data Optional data to pass to blocks
     * @return string The rendered HTML output
     */
    public function renderPage(string $area, string $page, array $data = []): string
    {
        return $this->renderPageContent($area, $page, $data);
    }
    
    /**
     * Render page content only (without wrapper)
     * 
     * Returns ONLY the page content that will be injected into {LAYOUT_CONTENT}
     * This allows iMSCP to handle variable replacement and wrapper application
     * 
     * @param string $area The area (admin, client, reseller)
     * @param string $page The page name (e.g., 'index')
     * @param array $data Optional data to pass to blocks
     * @return string The rendered content HTML
     */
    public function renderPageContent(string $area, string $page, array $data = []): string
    {
        try {
            // Dispatch before-render event
            $this->eventDispatcher->dispatch('page:before-render', [
                'area' => $area,
                'page' => $page,
                'data' => &$data
            ]);
            
            // Determine layout file name (e.g., admin_index)
            $layoutName = $area . '_' . $page;
            
            // Find layout file in theme hierarchy
            $layoutPath = $this->locateLayoutFile($layoutName);
            
            if (!$layoutPath) {
                throw new \RuntimeException("Layout file not found for: {$layoutName}");
            }
            
            // Store layout data in registry for blocks to access
            $this->registry->set('layout_data', $data);
            $this->registry->set('current_area', $area);
            $this->registry->set('current_page', $page);
            
            // Load and parse the layout XML
            $layoutXml = $this->layoutManager->load($layoutPath);
            
            // Build the block hierarchy
            $rootBlock = $this->layoutManager->buildLayout($layoutXml);
            
            if (!$rootBlock) {
                throw new \RuntimeException("Failed to build layout hierarchy");
            }
            
            // Render the root block (which renders all child blocks)
            $html = $this->templateEngine->renderBlock($rootBlock);
            
            // Dispatch after-render event
            $this->eventDispatcher->dispatch('page:after-render', [
                'area' => $area,
                'page' => $page,
                'html' => &$html
            ]);
            
            return $html;
            
        } catch (\Exception $e) {
            // Log the error
            if ($this->config['debug']) {
                return "<div class='error' style='color:red;padding:20px;margin:20px;border:1px solid red;'>"
                     . "<h3>DataBuilder Error</h3>"
                     . "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>"
                     . "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>"
                     . "</div>";
            }
            
            // In production, return empty or fallback
            error_log("DataBuilder Error: " . $e->getMessage());
            return "";
        }
    }
    
    /**
     * Locate a layout file in the theme hierarchy
     * 
     * Searches theme hierarchy: custom/ > default/ > base/
     * 
     * @param string $layoutName The layout name (e.g., 'admin_index')
     * @return string|null The path to the layout file or null if not found
     */
    private function locateLayoutFile(string $layoutName): ?string
    {
        $themePath = $this->config['themes_path'];
        $fileName = $layoutName . '.xml';
        
        // Determine current theme
        $theme = $this->config['theme'] ?? 'default';
        
        // Search hierarchy: custom > current theme > default > base
        $searchPaths = [
            $themePath . '/custom/layouts/' . $fileName,
            $themePath . '/' . $theme . '/layouts/' . $fileName,
            $themePath . '/default/layouts/' . $fileName,
            $themePath . '/base/layouts/' . $fileName,
        ];
        
        foreach ($searchPaths as $path) {
            // Normalize path separators
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
        }
        
        return null;
    }
}