<?php
/**
 * i-MSCP DataBuilderIMSCPPlugin
 *
 * Magento-like templating system for i-MSCP
 * Provides modular theming, layout blocks, and flexible template rendering
 *
 * @author        Jules MAHOUNOU <jtodjinou@datatechnologies.bj>
 * @copyright (C) 2024 Jules MAHOUNOU
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

/**
 * @noinspection PhpUnhandledExceptionInspection PhpDocMissingThrowsInspection
 */

/**
 * Class iMSCP_Plugin_DataBuilderIMSCPBetaPlugin
 */
class iMSCP_Plugin_DataBuilderIMSCPBetaPlugin extends iMSCP_Plugin_Action
{
    /**
     * Plugin name
     */
    const PLUGIN_NAME = 'DataBuilderIMSCPBetaPlugin';
    
    /**
     * @inheritDoc
     */
    public function init()
    {
        // Initialize autoloader for DataBuilder classes
        $this->initAutoloader();
        
        // Load translations if available
        $l10nDir = __DIR__ . '/l10n';
        if (is_dir($l10nDir)) {
            l10n_addTranslations($l10nDir . '/mo', 'Gettext', $this->getName());
        }
    }

    /**
     * Initialize DataBuilder autoloader
     */
    private function initAutoloader(): void
    {
        // Register DataBuilder namespace
        $vendorAutoload = __DIR__ . '/vendor/autoload.php';
        
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
        }
        
        // Register plugin src as PSR-4
        spl_autoload_register(function ($class) {
            $prefix = 'DataBuilder\\';
            $baseDir = __DIR__ . '/src/';
            
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require $file;
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function register(iMSCP_Events_Manager_Interface $events)
    {
        // Register event listeners for installation/update/enable
        $events->registerListener(
            [
                iMSCP_Events::onBeforeInstallPlugin,
                iMSCP_Events::onBeforeUpdatePlugin,
                iMSCP_Events::onBeforeEnablePlugin
            ],
            function (iMSCP_Events_Event $event) {
                $this->checkRequirements($event);
            }
        );

        // Template override hook (Magento-like theme fallback for non-DataBuilder pages)
        $events->registerListener(
            iMSCP_Events::onBeforeLoadTemplateFile,
            function (iMSCP_Events_Event $event) {
                $this->handleTemplateOverride($event);
            }
        );

        // Controller injection: replace template content with DataBuilder controller output
        $events->registerListener(
            iMSCP_Events::onAfterLoadTemplateFile,
            function (iMSCP_Events_Event $event) {
                $this->handleAfterTemplateLoad($event);
            }
        );

        // Register navigation for admin
        $events->registerListener(
            iMSCP_Events::onAdminScriptStart,
            function (iMSCP_Events_Event $event) {
                $this->setupAdminNavigation($event);
            }
        );

        // Register navigation for client
        $events->registerListener(
            iMSCP_Events::onClientScriptStart,
            function (iMSCP_Events_Event $event) {
                $this->setupClientNavigation($event);
            }
        );
    }


    /**
     * Handle template file override (Magento-like theme fallback)
     *
     * Only runs when DataBuilder theme is active. Redirects rootDir so iMSCP
     * loads custom overrides from the plugin themes directory when they exist.
     * When the default/other theme is active this method exits immediately,
     * leaving iMSCP behaviour completely untouched.
     *
     * IMPORTANT: pages that have a registered DataBuilder controller are SKIPPED
     * here entirely. Those pages are handled by handleAfterTemplateLoad() which
     * replaces the template content AFTER the file loads, without touching rootDir.
     * If we were to call getOverrideDirectory() for e.g. server_statistic, we
     * would find themes/custom/admin/server_statistic/ (the phtml blocks dir),
     * setRootDir() to themes/custom/admin, and then ALL subsequent templates
     * (layout, messages, nav...) would load from the wrong directory.
     *
     * @param iMSCP_Events_Event $event
     * @return void
     */
    private function handleTemplateOverride(iMSCP_Events_Event $event): void
    {
        // Only interfere when DataBuilder theme is explicitly active
        if (!$this->isDataBuilderThemeActive()) {
            return;
        }

        $context = $event->getParam('context');
        if (!$context instanceof \iMSCP\TemplateEngine) {
            return;
        }

        $templatePath = $event->getParam('templatePath');
        if (empty($templatePath)) {
            return;
        }

        $fileName = basename($templatePath, '.tpl');

        // Pages with a DataBuilder controller are handled by handleAfterTemplateLoad.
        // Do NOT touch rootDir for them — it would corrupt template loading globally.
        $controllerMap = [
            'server_statistic' => true,
        ];
        if (isset($controllerMap[$fileName])) {
            return;
        }

        // Use Reflection to read the protected $rootDir
        $reflection    = new \ReflectionClass($context);
        $rootDirProp   = $reflection->getProperty('rootDir');
        $rootDirProp->setAccessible(true);
        $rootDir = $rootDirProp->getValue($context);

        // Extract relative path and resolve a possible Magento-style override dir
        $relativePath = str_replace([$rootDir . '/', $rootDir . '\\'], '', $templatePath);
        $overrideDir  = $this->getOverrideDirectory($relativePath);

        if ($overrideDir !== null && is_dir($overrideDir)) {
            $context->setRootDir(dirname($overrideDir));
            $event->setParam('databuilder_original_root', $rootDir);
            $event->setParam('databuilder_override_dir', $overrideDir);
        }
    }

    /**
     * Inject DataBuilder controller output into a page template after it loads.
     *
     * Fires on onAfterLoadTemplateFile. When DataBuilder theme is active and a
     * controller is registered for the page, the controller renders its blocks
     * and the result replaces the raw template content.
     *
     * IMPORTANT: The content must be plain HTML — no <!-- BDP: xxx --> markers.
     * iMSCP's devide_dynamic() extracts BDP blocks and replaces them with {VAR}
     * placeholders in dtplData, so wrapping in BDP markers would cause LAYOUT_CONTENT
     * to become an unresolved {PAGE} placeholder instead of the DataBuilder HTML.
     *
     * @param iMSCP_Events_Event $event
     * @return void
     */
    private function handleAfterTemplateLoad(iMSCP_Events_Event $event): void
    {
        if (!$this->isDataBuilderThemeActive()) {
            return;
        }

        $context = $event->getParam('context');
        if (!$context instanceof \iMSCP\TemplateEngine) {
            return;
        }

        $templatePath = $event->getParam('templatePath');
        if (empty($templatePath)) {
            return;
        }

        // Only intercept page templates (admin/*, client/*, reseller/*), not layouts.
        $fileName = basename($templatePath, '.tpl');
        if (in_array($fileName, ['ui', 'index', 'layout', 'simple'], true)) {
            return;
        }

        // Static cache: devide_dynamic calls get_file once and caches dtplData, but
        // using a static cache here is a safeguard against any double invocation.
        static $resultCache = [];
        if (!array_key_exists($fileName, $resultCache)) {
            $resultCache[$fileName] = $this->executeDataBuilderController($fileName, $context);
        }

        $controllerResult = $resultCache[$fileName];
        if ($controllerResult === null) {
            return;
        }

        error_log('DataBuilder: injecting controller output for ' . $fileName . ' (' . strlen($controllerResult) . ' bytes)');

        // Plain HTML — no BDP/EDP wrapper. devide_dynamic will see no block markers
        // and return the HTML as-is, so LAYOUT_CONTENT receives the full output.
        $event->setParam('templateContent', $controllerResult);
    }

    /**
     * Execute DataBuilder controller if one exists for the page
     * 
     * @param string $pageName Page name (template name without extension)
     * @param \iMSCP\TemplateEngine $context Template engine context
     * @return string|null Controller output or null if no controller exists
     */
    private function executeDataBuilderController(string $pageName, \iMSCP\TemplateEngine $context): ?string
    {
        // Map page names to controller classes
        $controllerMap = [
            'server_statistic' => 'DataBuilder\\Controller\\ServerStatisticController',
            // Add more page -> controller mappings here
        ];
        
        if (!isset($controllerMap[$pageName])) {
            return null;
        }
        
        $controllerClass = $controllerMap[$pageName];
        
        // Check if controller class exists
        if (!class_exists($controllerClass)) {
            return null;
        }
        
        try {
            // Initialize DataBuilder components
            $config = $this->getDataBuilderConfig($context);
            
            $registry = new \DataBuilder\Core\Registry();
            $registry->set('config', $config);
            
            $layoutManager = new \DataBuilder\Layout\LayoutManager($config, $registry);
            $blockFactory = new \DataBuilder\Block\BlockFactory($registry);
            
            $templateConfig = [
                'themes_path'  => $config['themes_path'],
                'theme'        => $config['theme'],
                'modules_path' => $config['modules_path'],
                'cache_path'   => $config['cache_path'],
                'cache_enable' => $config['cache_enable'],
            ];
            
            $templateEngine = new \DataBuilder\Template\TemplateEngine($templateConfig);
            
            // Create route object (method, path, controller, action, handle, params)
            $route = new \DataBuilder\Router\Route(
                'GET',
                '/admin/' . $pageName,
                $controllerClass,
                'execute',
                $pageName,
                []
            );
            
            // Instantiate controller
            $controller = new $controllerClass(
                $route,
                $registry,
                $layoutManager,
                $blockFactory,
                $templateEngine
            );
            
            // Execute controller and return output
            return $controller->execute();
            
        } catch (\Throwable $e) {
            // Log error but don't break the page
            error_log('DataBuilder Controller Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get DataBuilder configuration from i-MSCP context
     * 
     * @param \iMSCP\TemplateEngine $context
     * @return array Configuration array
     */
    private function getDataBuilderConfig(\iMSCP\TemplateEngine $context): array
    {
        $imscpRoot = $this->findImscpRoot();
        
        return [
            'gui_path'          => $imscpRoot . '/gui',
            'plugin_path'      => __DIR__,
            'theme'            => 'databuilder',
            'themes_path'      => __DIR__ . '/themes',
            'modules_path'     => __DIR__ . '/modules',
            'cache_path'       => $imscpRoot . '/gui/data/cache/databuilder-templates',
            'cache_enable'     => false,
        ];
    }
    
    /**
     * Get the override directory for a template
     * 
     * @param string $relativePath Relative template path (e.g., admin/admin_log.tpl)
     * @return string|null Override directory path or null if not found
     */
    private function getOverrideDirectory(string $relativePath): ?string
    {
        // Remove .tpl extension
        $templateName = str_replace('.tpl', '', $relativePath);
        
        // Check in plugin themes directory
        $pluginThemesDir = __DIR__ . '/themes';
        
        // Get current i-MSCP theme
        $imscpTheme = $this->getCurrentImscpTheme();
        
        // Theme fallback order (like Magento):
        // 1. Custom theme (highest priority)
        // 2. Default theme
        // 3. Base theme (lowest priority)
        
        $themeHierarchy = ['custom', 'default', 'base'];
        
        foreach ($themeHierarchy as $theme) {
            $themeDir = $pluginThemesDir . '/' . $theme;
            
            if (!is_dir($themeDir)) {
                continue;
            }
            
            // Check for template override in theme
            $templateDir = $themeDir . '/' . $templateName;
            
            if (is_dir($templateDir)) {
                return $templateDir;
            }
        }
        
        return null;
    }
    
    /**
     * Get current i-MSCP theme
     * 
     * Tries multiple methods to detect the current theme:
     * 1. From session user_theme
     * 2. From config ROOT_TEMPLATE_PATH
     * 3. From config USER_INITIAL_THEME
     * 
     * @return string The current theme name
     */
    private function getCurrentImscpTheme(): string
    {
        // Method 1: Try session first
        if (!empty($_SESSION['user_theme'])) {
            return $_SESSION['user_theme'];
        }
        
        // Method 2: Try config ROOT_TEMPLATE_PATH
        try {
            $cfg = \iMSCP\Registry::get('config');
            
            if (!empty($cfg['ROOT_TEMPLATE_PATH'])) {
                // Extract theme name from path like /var/www/imscp/gui/themes/databuilder
                $path = $cfg['ROOT_TEMPLATE_PATH'];
                $themeName = basename($path);
                if (!empty($themeName)) {
                    return $themeName;
                }
            }
            
            // Method 3: Fallback to default theme
            return $cfg['USER_INITIAL_THEME'] ?? 'default';
            
        } catch (\Exception $e) {
            // If registry is not available, return default
            return 'default';
        }
    }
    
    /**
     * Check if DataBuilder theme is currently active
     * 
     * @return bool True if DataBuilder theme is the active theme
     */
    private function isDataBuilderThemeActive(): bool
    {
        $currentTheme = $this->getCurrentImscpTheme();
        $isDatabuilder = $currentTheme === 'databuilder';
        
        // Debug output to PHP error log
        error_log('=== DataBuilder Theme Check ===');
        error_log('Current theme detected: ' . $currentTheme);
        error_log('Is DataBuilder theme active: ' . ($isDatabuilder ? 'YES' : 'NO'));
        error_log('Session user_theme: ' . ($_SESSION['user_theme'] ?? 'NOT SET'));
        
        try {
            $cfg = \iMSCP\Registry::get('config');
            error_log('Config ROOT_TEMPLATE_PATH: ' . ($cfg['ROOT_TEMPLATE_PATH'] ?? 'NOT SET'));
        } catch (\Exception $e) {
            error_log('Could not get config: ' . $e->getMessage());
        }
        error_log('==============================');
        
        return $isDatabuilder;
    }

    /**
     * @inheritDoc
     */
    public function install(iMSCP_Plugin_Manager $pluginManager)
    {
        try {
            // Create necessary directories in plugin
            $this->createDirectories();
            
            // Install vendor dependencies if needed
            $this->installDependencies();
            
            // Install DataBuilder theme in iMSCP themes directory
            $this->installTheme($pluginManager);
            
            // Install TemplateEngine hook for DataBuilder functionality
            $this->installTemplateEngineHook();
            
            write_log('DataBuilderIMSCPPlugin installed successfully', E_USER_NOTICE);
        } catch (Exception $e) {
            throw new iMSCP_Plugin_Exception(
                $e->getMessage(), $e->getCode(), $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function update(
        iMSCP_Plugin_Manager $pluginManager, $fromVersion, $toVersion
    )
    {
        try {
            // Update dependencies if needed
            $this->installDependencies();
            
            // Update theme files (preserves customizations)
            $this->installTheme($pluginManager);
            
            // Re-install TemplateEngine hook (in case of updates)
            $this->installTemplateEngineHook();
            
            write_log('DataBuilderIMSCPPlugin updated to version ' . $toVersion, E_USER_NOTICE);
        } catch (Exception $e) {
            throw new iMSCP_Plugin_Exception(
                $e->getMessage(), $e->getCode(), $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function uninstall(iMSCP_Plugin_Manager $pluginManager)
    {
        try {
            // Remove DataBuilder theme from iMSCP themes directory
            $this->uninstallTheme($pluginManager);
            
            // Remove TemplateEngine hook
            $this->uninstallTemplateEngineHook();
            
            // Clean up if needed (but keep user data)
            write_log('DataBuilderIMSCPPlugin uninstalled', E_USER_NOTICE);
        } catch (Exception $e) {
            throw new iMSCP_Plugin_Exception(
                $e->getMessage(), $e->getCode(), $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getRoutes()
    {
        // Get iMSCP root
        $imscpRoot = $this->findImscpRoot();
        $themesPath = $imscpRoot . '/gui/themes/databuilder/admin';
        
        // Point to the copied files in themes directory
        $routes = [
            '/admin/databuilder' => $themesPath . '/databuilder.php',
            '/admin/databuilder-test' => $themesPath . '/test.php',
            '/admin/databuilder-minimal' => $themesPath . '/minimal_test.php',
            '/admin/databuilder-server-stats' => $themesPath . '/server_stats.php',
            '/admin/server_statistic' => $themesPath . '/server_statistic.php',
            '/client/databuilder' => $imscpRoot . '/gui/themes/databuilder/client/databuilder.php',
            '/databuilder' => $imscpRoot . '/gui/themes/databuilder/shared/databuilder.php',
        ];

        return $routes;
    }

    /**
     * Check requirements
     *
     * @param iMSCP_Events_Event $event
     * @return void
     */
    private function checkRequirements(iMSCP_Events_Event $event)
    {
        if ($event->getParam('pluginName') != $this->getName()) {
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            set_page_message(tr(
                'DataBuilderIMSCPPlugin requires PHP 7.4 or higher.'
            ), 'error');
            $event->stopPropagation();
            return;
        }

        // Check for required extensions
        $requiredExtensions = ['pdo', 'json', 'mbstring', 'xml'];
        $missingExtensions = [];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (!empty($missingExtensions)) {
            set_page_message(tr(
                'DataBuilderIMSCPPlugin requires the following PHP extensions: %s',
                implode(', ', $missingExtensions)
            ), 'error');
            $event->stopPropagation();
        }
    }

    /**
     * Setup admin plugin navigation
     *
     * @param iMSCP_Events_Event $event
     * @return void
     */
    private function setupAdminNavigation(iMSCP_Events_Event $event)
    {
        if (is_xhr() || !iMSCP_Registry::isRegistered('navigation')) {
            return;
        }

        /** @var Zend_Navigation $navigation */
        $navigation = iMSCP_Registry::get('navigation');

        if ($page = $navigation->findOneBy('uri', '/admin/settings.php')) {
            $page->addPage([
                'label'       => tr('DataBuilder'),
                'uri'         => '/admin/databuilder',
                'title_class' => 'settings',
                'order'       => 9
            ]);
        }
    }

    /**
     * Setup client plugin navigation
     *
     * @param iMSCP_Events_Event $event
     * @return void
     */
    private function setupClientNavigation(iMSCP_Events_Event $event)
    {
        if (is_xhr() || !iMSCP_Registry::isRegistered('navigation')) {
            return;
        }

        // Get plugin config
        $config = $this->getConfig();
        
        // Only add navigation if enabled for clients
        if (empty($config['enable_for_clients']) || $config['enable_for_clients'] !== 'yes') {
            return;
        }

        /** @var Zend_Navigation $navigation */
        $navigation = iMSCP_Registry::get('navigation');

        // Add to user home or general section
        if ($page = $navigation->findOneBy('uri', '/client/index.php')) {
            $page->addPage([
                'label'       => tr('DataBuilder'),
                'uri'         => '/client/databuilder',
                'title_class' => 'databuilder',
                'order'       => 99
            ]);
        }
    }

    /**
     * Create necessary directories
     *
     * @return void
     */
    private function createDirectories(): void
    {
        $directories = [
            __DIR__ . '/cache',
            __DIR__ . '/data',
            __DIR__ . '/themes/base',
            __DIR__ . '/themes/default',
            __DIR__ . '/themes/custom',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Install vendor dependencies via Composer
     * 
     * @return void
     */
    private function installDependencies(): void
    {
        $vendorDir = __DIR__ . '/vendor';
        
        if (!is_dir($vendorDir)) {
            // Try to install dependencies
            $composerLock = __DIR__ . '/composer.lock';
            
            if (file_exists($composerLock)) {
                // Run composer install
                $composerJson = __DIR__ . '/composer.json';
                if (file_exists($composerJson)) {
                    // Note: This would need to be run manually in production
                    // For now, we'll create a minimal autoloader
                    $this->createMinimalAutoloader();
                }
            }
        }
    }
    
    /**
     * Create minimal autoloader if Composer is not available
     * 
     * @return void
     */
    private function createMinimalAutoloader(): void
    {
        $vendorDir = __DIR__ . '/vendor';
        
        if (!is_dir($vendorDir)) {
            mkdir($vendorDir, 0755, true);
        }
        
        $autoloadContent = '<?php
/**
 * Minimal autoloader for DataBuilderIMSCPPlugin
 * This is used when Composer is not available
 */

spl_autoload_register(function ($class) {
    $prefixes = [
        "DataBuilder\\\\" => __DIR__ . "/src/",
    ];
    
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace("\\\\", "/", $relativeClass) . ".php";
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Register template functions
function databuilder_render_template($template, $data = []) {
    extract($data);
    
    $templateFile = __DIR__ . "/themes/" . $template . ".phtml";
    
    if (file_exists($templateFile)) {
        include $templateFile;
    }
}

function databuilder_get_template_content($template, $data = []) {
    ob_start();
    databuilder_render_template($template, $data);
    return ob_get_clean();
}
';
        
        file_put_contents($vendorDir . '/autoload.php', $autoloadContent);
    }
    
    /**
     * Install DataBuilder theme in iMSCP themes directory
     * 
     * This creates a symlink or copies the theme files to gui/themes/databuilder/
     * 
     * @param iMSCP_Plugin_Manager $pluginManager
     * @return void
     */
    private function installTheme(iMSCP_Plugin_Manager $pluginManager): void
    {
        // Get iMSCP root directory
        $imscpRoot = $this->findImscpRoot();
        $themesDir = $imscpRoot . '/gui/themes';
        $databuilderThemeDir = $themesDir . '/databuilder';
        
        // Create themes directory if not exists
        if (!is_dir($themesDir)) {
            mkdir($themesDir, 0755, true);
        }
        
        // Check if databuilder theme already exists
        if (is_dir($databuilderThemeDir)) {
            // Backup custom theme if exists before overwriting
            $customBackupDir = $databuilderThemeDir . '_backup_' . time();
            if (is_dir($databuilderThemeDir . '/custom')) {
                $this->copyDirectory(
                    $databuilderThemeDir . '/custom',
                    $customBackupDir . '/custom'
                );
            }
        }
        
        // Create theme directory
        if (!is_dir($databuilderThemeDir)) {
            mkdir($databuilderThemeDir, 0755, true);
        }
        
        // Copy theme files from plugin
        $pluginThemesDir = __DIR__ . '/themes';
        
        // Files to copy (not directories)
        $filesToCopy = [
            'info.php',
            'index.tpl',
            'login.tpl',
            'lostpassword.tpl',
            'message.tpl',
            'functions.php',
        ];
        
        foreach ($filesToCopy as $file) {
            $source = $pluginThemesDir . '/' . $file;
            if (file_exists($source)) {
                copy($source, $databuilderThemeDir . '/' . $file);
            }
        }
        
        // Copy admin .tpl overrides (server_statistic.tpl, index.tpl, etc.)
        // These must exist in the databuilder theme dir so iMSCP's is_safe() check passes.
        $pluginAdminTplDir = $pluginThemesDir . '/admin';
        $themeAdminDir     = $databuilderThemeDir . '/admin';
        if (is_dir($pluginAdminTplDir)) {
            if (!is_dir($themeAdminDir)) {
                mkdir($themeAdminDir, 0755, true);
            }
            $this->copyDirectory($pluginAdminTplDir, $themeAdminDir);
        }

        // Copy templates directory (generic templates for blocks)
        $pluginTemplatesDir = $pluginThemesDir . '/templates';
        $themeTemplatesDir = $databuilderThemeDir . '/templates';
        
        if (is_dir($pluginTemplatesDir)) {
            if (!is_dir($themeTemplatesDir)) {
                mkdir($themeTemplatesDir, 0755, true);
            }
            $this->copyDirectory($pluginTemplatesDir, $themeTemplatesDir);
        }
        
        // Create symlink or copy admin directory
        $pluginAdminDir = $pluginThemesDir . '/templates/admin';
        $themeAdminDir = $databuilderThemeDir . '/admin';
        
        if (is_dir($pluginAdminDir)) {
            if (!is_dir($themeAdminDir)) {
                mkdir($themeAdminDir, 0755, true);
            }
            $this->copyDirectory($pluginAdminDir, $themeAdminDir);
        }
        
        // Create client directory if exists
        $pluginClientDir = $pluginThemesDir . '/client';
        $themeClientDir = $databuilderThemeDir . '/client';
        
        if (is_dir($pluginClientDir)) {
            if (!is_dir($themeClientDir)) {
                mkdir($themeClientDir, 0755, true);
            }
            $this->copyDirectory($pluginClientDir, $themeClientDir);
        } else {
            if (!is_dir($themeClientDir)) {
                mkdir($themeClientDir, 0755, true);
            }
        }
        
        // Create reseller directory if exists
        $pluginResellerDir = $pluginThemesDir . '/reseller';
        $themeResellerDir = $databuilderThemeDir . '/reseller';
        
        if (is_dir($pluginResellerDir)) {
            if (!is_dir($themeResellerDir)) {
                mkdir($themeResellerDir, 0755, true);
            }
            $this->copyDirectory($pluginResellerDir, $themeResellerDir);
        } else {
            if (!is_dir($themeResellerDir)) {
                mkdir($themeResellerDir, 0755, true);
            }
        }
        
        // Create layouts directory (contains layout XML files)
        $pluginLayoutsDir = $pluginThemesDir . '/layouts';
        $themeLayoutsDir = $databuilderThemeDir . '/layouts';
        
        if (is_dir($pluginLayoutsDir)) {
            if (!is_dir($themeLayoutsDir)) {
                mkdir($themeLayoutsDir, 0755, true);
            }
            $this->copyDirectory($pluginLayoutsDir, $themeLayoutsDir);
        } else {
            if (!is_dir($themeLayoutsDir)) {
                mkdir($themeLayoutsDir, 0755, true);
            }
        }
        
        // Create shared layouts symlink/copy
        $pluginSharedDir = $pluginThemesDir . '/shared';
        $themeSharedDir = $databuilderThemeDir . '/shared';
        
        if (is_dir($pluginSharedDir)) {
            if (!is_dir($themeSharedDir)) {
                mkdir($themeSharedDir, 0755, true);
            }
            $this->copyDirectory($pluginSharedDir, $themeSharedDir);
        }
        
        // Create/update assets directory (always copy on install/update)
        $assetsDir = $databuilderThemeDir . '/assets';
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }
        
        // Copy or create assets
        $pluginAssetsDir = __DIR__ . '/assets';
        if (is_dir($pluginAssetsDir)) {
            $this->copyDirectory($pluginAssetsDir, $assetsDir);
        } else {
            // Create placeholder .gitkeep if no assets directory in plugin
            if (!file_exists($assetsDir . '/.gitkeep')) {
                file_put_contents($assetsDir . '/.gitkeep', '');
            }
        }
        
        // Copy frontend admin files to themes/databuilder/admin (all admin files in one place)
        $databuilderThemeAdminDir = $databuilderThemeDir . '/admin';
        
        $pluginFrontendAdminDir = __DIR__ . '/frontend/admin';
        
        if (is_dir($pluginFrontendAdminDir)) {
            // Create admin directory if it doesn't exist
            if (!is_dir($databuilderThemeAdminDir)) {
                mkdir($databuilderThemeAdminDir, 0755, true);
            }
            
            // Copy all files from frontend/admin
            $files = scandir($pluginFrontendAdminDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $source = $pluginFrontendAdminDir . '/' . $file;
                $dest = $databuilderThemeAdminDir . '/' . $file;
                
                if (is_file($source)) {
                    copy($source, $dest);
                } elseif (is_dir($source)) {
                    if (!is_dir($dest)) {
                        mkdir($dest, 0755, true);
                    }
                    $this->copyDirectory($source, $dest);
                }
            }
        }
        
        write_log('DataBuilder theme installed in: ' . $databuilderThemeDir, E_USER_NOTICE);
    }
    
    /**
     * Find iMSCP root directory
     * 
     * @return string
     */
    public function findImscpRoot(): string
    {
        // Try multiple possible paths
        $possiblePaths = [
            dirname(__DIR__, 3),                    // From plugin: gui/plugins -> gui -> .
            $_SERVER['DOCUMENT_ROOT'] ?? '',        // Document root
            '/var/www/imscp',                      // Common default
            '/var/www/html/imscp',
        ];
        
        foreach ($possiblePaths as $path) {
            if (empty($path)) continue;
            $testFile = $path . '/gui/include/imscp-lib.php';
            if (file_exists($testFile)) {
                return $path;
            }
        }
        
        // Fallback
        return '/var/www/imscp';
    }
    
    /**
     * Copy directory recursively
     * 
     * @param string $source
     * @param string $destination
     * @return void
     */
    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') continue;
            
            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;
            
            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
        closedir($dir);
    }
    
    /**
     * Uninstall DataBuilder theme from iMSCP themes directory
     * 
     * This removes the theme directory from gui/themes/databuilder/
     * 
     * @param iMSCP_Plugin_Manager $pluginManager
     * @return void
     */
    private function uninstallTheme(iMSCP_Plugin_Manager $pluginManager): void
    {
        // Get iMSCP root directory
        $imscpRoot = $this->findImscpRoot();
        $themesDir = $imscpRoot . '/gui/themes';
        $databuilderThemeDir = $themesDir . '/databuilder';
        
        // Remove databuilder theme directory
        if (is_dir($databuilderThemeDir)) {
            // First, backup custom theme if exists (in case of re-installation)
            $customDir = $databuilderThemeDir . '/custom';
            if (is_dir($customDir)) {
                $backupDir = $themesDir . '/databuilder_custom_backup_' . time();
                $this->copyDirectory($customDir, $backupDir);
            }
            
            // Remove the theme directory
            $this->removeDirectory($databuilderThemeDir);
            
            write_log('DataBuilder theme uninstalled from: ' . $databuilderThemeDir, E_USER_NOTICE);
        }
    }
    
    /**
     * Remove directory recursively
     * 
     * @param string $dir
     * @return void
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }

    /**
     * Install TemplateEngine hook for DataBuilder functionality
     * 
     * This copies the TemplateEngineDatabuilderHook.php file to the iMSCP gui/src directory
     * to provide getRootDir() method needed for DataBuilder template override system.
     * 
     * @return void
     */
    private function installTemplateEngineHook(): void
    {
        $imscpRoot = $this->findImscpRoot();
        $srcDir = $imscpRoot . '/gui/src';
        $hookSource = __DIR__ . '/hooks/TemplateEngineDatabuilderHook.php';
        $hookDest = $srcDir . '/TemplateEngineDatabuilderHook.php';
        
        // Ensure source directory exists
        if (!is_dir($srcDir)) {
            mkdir($srcDir, 0755, true);
        }
        
        // Copy the hook file
        if (file_exists($hookSource)) {
            copy($hookSource, $hookDest);
            write_log('DataBuilder TemplateEngine hook installed in: ' . $hookDest, E_USER_NOTICE);
        } else {
            write_log('DataBuilder TemplateEngine hook source not found: ' . $hookSource, E_USER_WARNING);
        }
    }

    /**
     * Uninstall TemplateEngine hook
     * 
     * This removes the TemplateEngineDatabuilderHook.php file from the iMSCP gui/src directory
     * 
     * @return void
     */
    private function uninstallTemplateEngineHook(): void
    {
        $imscpRoot = $this->findImscpRoot();
        $hookFile = $imscpRoot . '/gui/src/TemplateEngineDatabuilderHook.php';
        
        if (file_exists($hookFile)) {
            unlink($hookFile);
            write_log('DataBuilder TemplateEngine hook removed from: ' . $hookFile, E_USER_NOTICE);
        }
    }
}
