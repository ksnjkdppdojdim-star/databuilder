<?php
/**
 * DataBuilder ImscpPageController
 * 
 * Handles dynamic page routing for iMSCP integration
 * Checks if DataBuilder has the page, otherwise delegates to iMSCP
 */

namespace DataBuilder\Controller;

class ImscpPageController
{
    private $config;
    private $pluginDir;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->pluginDir = dirname(__DIR__, 2);
    }
    
    /**
     * Dispatch request - check if DataBuilder has the page
     * 
     * @param string $page Page name (e.g., 'admin_log')
     * @param string $area Area ('admin', 'client', 'reseller')
     * @return string|null Template path or null to delegate to iMSCP
     */
    public function dispatch(string $page, string $area = 'admin'): ?string
    {
        // 1. Check if DataBuilder has layout: themes/{$area}/{$page}.xml
        // 2. Check if DataBuilder has template: themes/{$area}/{$page}.phtml
        // 3. If YES → Return template path for DataBuilder to render
        // 4. If NO → Return null to let iMSCP handle it
        
        $templatePath = $this->findTemplate($page, $area);
        
        if ($templatePath !== null) {
            // DataBuilder has the page - render with DataBuilder
            return $templatePath;
        }
        
        // Not found in DataBuilder - delegate to iMSCP
        return null;
    }
    
    /**
     * Find template in DataBuilder theme
     * 
     * @param string $page Page name
     * @param string $area Area (admin, client, reseller)
     * @return string|null Template path or null
     */
    public function findTemplate(string $page, string $area): ?string
    {
        $themesPath = $this->pluginDir . '/themes';
        
        // Priority order (like Magento):
        // 1. Custom theme (highest priority)
        // 2. Default theme
        // 3. Base theme
        
        $searchPaths = [
            $themesPath . '/custom/templates/' . $area . '/' . $page . '.phtml',
            $themesPath . '/default/templates/' . $area . '/' . $page . '.phtml',
            $themesPath . '/base/templates/' . $area . '/' . $page . '.phtml',
            
            // Also check in view folder
            $themesPath . '/default/view/' . $area . '/' . $page . '.phtml',
            $themesPath . '/base/view/' . $area . '/' . $page . '.phtml',
        ];
        
        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Find layout in DataBuilder theme
     * 
     * @param string $page Page name
     * @param string $area Area (admin, client, reseller)
     * @return string|null Layout path or null
     */
    public function findLayout(string $page, string $area): ?string
    {
        $themesPath = $this->pluginDir . '/themes';
        
        $searchPaths = [
            $themesPath . '/custom/layouts/' . $area . '/' . $page . '.xml',
            $themesPath . '/default/layouts/' . $area . '/' . $page . '.xml',
            $themesPath . '/base/layouts/' . $area . '/' . $page . '.xml',
            
            // Also check layouts folder
            $themesPath . '/custom/layouts/' . $page . '.xml',
            $themesPath . '/default/layouts/' . $page . '.xml',
            $themesPath . '/base/layouts/' . $page . '.xml',
        ];
        
        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
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
        
        // Try to find iMSCP root
        $imscpRoot = $this->findImscpRoot();
        
        return $imscpRoot . '/gui/themes/' . $imscpTheme . '/' . $template . '.tpl';
    }
    
    /**
     * Get current iMSCP theme
     * 
     * @return string
     */
    private function getCurrentImscpTheme(): string
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
     * Check if a page exists in DataBuilder
     * 
     * @param string $page Page name
     * @param string $area Area (admin, client, reseller)
     * @return bool
     */
    public function hasPage(string $page, string $area = 'admin'): bool
    {
        return $this->findTemplate($page, $area) !== null;
    }
    
    /**
     * Get list of available pages in DataBuilder
     * 
     * @param string $area Area (admin, client, reseller)
     * @return array List of page names
     */
    public function getAvailablePages(string $area = 'admin'): array
    {
        $pages = [];
        $themesPath = $this->pluginDir . '/themes';
        
        // Check each theme level
        $themeLevels = ['custom', 'default', 'base'];
        
        foreach ($themeLevels as $level) {
            $templatesDir = $themesPath . '/' . $level . '/templates/' . $area;
            $viewDir = $themesPath . '/' . $level . '/view/' . $area;
            
            foreach ([$templatesDir, $viewDir] as $dir) {
                if (is_dir($dir)) {
                    $files = glob($dir . '/*.phtml');
                    if ($files) {
                        foreach ($files as $file) {
                            $pageName = basename($file, '.phtml');
                            if (!in_array($pageName, $pages)) {
                                $pages[] = $pageName;
                            }
                        }
                    }
                }
            }
        }
        
        return $pages;
    }
}

