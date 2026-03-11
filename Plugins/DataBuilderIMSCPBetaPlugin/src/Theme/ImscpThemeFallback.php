<?php
/**
 * DataBuilder ImscpThemeFallback
 * 
 * Handles theme fallback system for iMSCP integration
 * If DataBuilder doesn't have a page, delegates to iMSCP themes
 */

namespace DataBuilder\Theme;

class ImscpThemeFallback
{
    private $config;
    private $pluginDir;
    
    public function __construct(array $config = [], string $pluginDir = '')
    {
        $this->config = $config;
        $this->pluginDir = $pluginDir ?: dirname(__DIR__, 2);
    }
    
    /**
     * Resolve template path with fallback to iMSCP themes
     * 
     * @param string $template Template name (e.g., 'admin/admin_log')
     * @param string $area Area (admin, client, reseller)
     * @return string|null Template path or null to delegate to iMSCP
     */
    public function resolve(string $template, string $area = 'admin'): ?string
    {
        // Priority order (like Magento):
        // 1. Custom theme (highest priority)
        // 2. Default theme
        // 3. Base theme
        // 4. Delegate to iMSCP (return null)
        
        $themesPath = $this->pluginDir . '/themes';
        
        $searchPaths = [
            // Custom theme (highest priority)
            $themesPath . '/custom/templates/' . $template . '.phtml',
            $themesPath . '/custom/view/' . $area . '/' . $template . '.phtml',
            
            // Default theme
            $themesPath . '/default/templates/' . $template . '.phtml',
            $themesPath . '/default/view/' . $area . '/' . $template . '.phtml',
            
            // Base theme (lowest priority)
            $themesPath . '/base/templates/' . $template . '.phtml',
            $themesPath . '/base/view/' . $area . '/' . $template . '.phtml',
        ];
        
        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Not found in DataBuilder → Delegate to iMSCP (return null)
        return null;
    }
    
    /**
     * Get iMSCP theme path for fallback
     * 
     * @param string $template Template name (e.g., 'admin/admin_log')
     * @return string Path to iMSCP theme template
     */
    public function getImscpThemePath(string $template): string
    {
        $imscpTheme = $this->getCurrentImscpTheme();
        $imscpRoot = $this->findImscpRoot();
        
        return $imscpRoot . '/gui/themes/' . $imscpTheme . '/' . $template . '.tpl';
    }
    
    /**
     * Get list of available iMSCP themes
     * 
     * @return array
     */
    public function getAvailableImscpThemes(): array
    {
        $imscpRoot = $this->findImscpRoot();
        $themesDir = $imscpRoot . '/gui/themes';
        
        $themes = [];
        if (is_dir($themesDir)) {
            $items = scandir($themesDir);
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($themesDir . '/' . $item)) {
                    $themes[] = $item;
                }
            }
        }
        
        return $themes;
    }
    
    /**
     * Get current iMSCP theme
     * 
     * @return string
     */
    public function getCurrentImscpTheme(): string
    {
        if (defined('IMSCP_THEME')) {
            return IMSCP_THEME;
        }
        
        return 'default';
    }
    
    /**
     * Find iMSCP root directory
     * 
     * @return string
     */
    private function findImscpRoot(): string
    {
        // Try multiple possible paths
        $possiblePaths = [
            dirname($this->pluginDir, 2),           // gui/plugins -> gui
            dirname($this->pluginDir, 3),           // gui/plugins -> .
            $_SERVER['DOCUMENT_ROOT'] ?? '',        // Document root
            '/var/www/imscp',                       // Common default
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
     * Check if DataBuilder has a specific page
     * 
     * @param string $page Page name
     * @param string $area Area (admin, client, reseller)
     * @return bool
     */
    public function hasDataBuilderPage(string $page, string $area = 'admin'): bool
    {
        return $this->resolve($page, $area) !== null;
    }
    
    /**
     * Get all available pages from DataBuilder themes
     * 
     * @param string $area Area (admin, client, reseller)
     * @return array
     */
    public function getDataBuilderPages(string $area = 'admin'): array
    {
        $pages = [];
        $themesPath = $this->pluginDir . '/themes';
        
        // Check each theme level
        $themeLevels = ['custom', 'default', 'base'];
        
        foreach ($themeLevels as $level) {
            $dirs = [
                $themesPath . '/' . $level . '/templates/' . $area,
                $themesPath . '/' . $level . '/view/' . $area,
            ];
            
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $files = glob($dir . '/*.phtml');
                    if ($files) {
                        foreach ($files as $file) {
                            $pageName = basename($file, '.phtml');
                            if (!isset($pages[$pageName])) {
                                $pages[$pageName] = [
                                    'name' => $pageName,
                                    'theme' => $level,
                                    'path' => $file
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $pages;
    }
}

