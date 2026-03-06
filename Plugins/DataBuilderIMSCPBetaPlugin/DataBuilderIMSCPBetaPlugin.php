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

        // TEMPORARILY DISABLED: Template override hook requires getRootDir() method
        // which doesn't exist in iMSCP\TemplateEngine
        // TODO: Re-enable once we find an alternative approach
        /*
        $events->registerListener(
            iMSCP_Events::onBeforeLoadTemplateFile,
            function (iMSCP_Events_Event $event) {
                $this->handleTemplateOverride($event);
            }
        );
        */
        
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
     * This intercepts template loading and checks if there's a DataBuilder
     * override in the theme directory
     *
     * @param iMSCP_Events_Event $event
     * @return void
     */
    private function handleTemplateOverride(iMSCP_Events_Event $event): void
    {
        $templatePath = $event->getParam('templatePath');
        
        if (empty($templatePath)) {
            return;
        }
        
        // Get template engine context
        $context = $event->getParam('context');
        if (!$context instanceof \iMSCP\TemplateEngine) {
            return;
        }
        
        // Extract template name from path
        // e.g., /path/to/themes/default/admin/admin_log.tpl -> admin/admin_log
        $rootDir = $context->getRootDir();
        $relativePath = str_replace($rootDir . '/', '', $templatePath);
        
        // Check if this is a .tpl file that can be overridden
        if (strpos($relativePath, '.tpl') === false) {
            return;
        }
        
        // Convert admin_log.tpl to admin_log/ directory
        $overrideDir = $this->getOverrideDirectory($relativePath);
        
        if ($overrideDir !== null && is_dir($overrideDir)) {
            // Override found! Modify the template path
            $context->setRootDir(dirname($overrideDir));
            
            // The dynamic template name will be the folder name
            $folderName = basename($overrideDir);
            
            // Store original root dir for later restore
            $event->setParam('databuilder_original_root', $rootDir);
            $event->setParam('databuilder_override_dir', $overrideDir);
        }
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
     * @return string
     */
    private function getCurrentImscpTheme(): string
    {
        if (defined('IMSCP_THEME')) {
            return IMSCP_THEME;
        }
        
        // Try to get from config
        try {
            if (iMSCP_Registry::isRegistered('config')) {
                $config = iMSCP_Registry::get('config');
                if (isset($config['USER_THEME'])) {
                    return $config['USER_THEME'];
                }
            }
        } catch (\Exception $e) {
            // Config not available
        }
        
        return 'default';
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
}
