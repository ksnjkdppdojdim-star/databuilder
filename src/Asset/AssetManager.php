<?php
/**
 * DataBuilder Asset Manager
 * 
 * Manages CSS, JavaScript, and other assets with theme hierarchy support.
 * Supports custom/default/base theme structure.
 */

namespace DataBuilder\Asset;

class AssetManager
{
    /**
     * Collected CSS files
     * @var array
     */
    protected $cssFiles = [];
    
    /**
     * Collected inline CSS
     * @var array
     */
    protected $cssInline = [];
    
    /**
     * Collected JavaScript files
     * @var array
     */
    protected $jsFiles = [];
    
    /**
     * Collected inline JavaScript
     * @var array
     */
    protected $jsInline = [];
    
    /**
     * Base configuration
     * @var array
     */
    protected $config = [];
    
    /**
     * Initialize AssetManager
     * 
     * @param array $config Configuration with themes_path, etc.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
    
    /**
     * Add a CSS file
     * 
     * Searches theme hierarchy: custom > default > base
     * 
     * @param string $filename CSS filename
     * @param string $area Optional area (admin, client, reseller)
     * @return void
     */
    public function addCss(string $filename, string $area = ''): void
    {
        $file = $this->findAssetFile('css', $filename, $area);
        
        if ($file) {
            $this->cssFiles[$filename] = $file;
        }
    }
    
    /**
     * Add inline CSS
     * 
     * @param string $css CSS content
     * @param string $id Optional identifier for uniqueness
     * @return void
     */
    public function addCssInline(string $css, string $id = ''): void
    {
        $this->cssInline[$id ?: count($this->cssInline)] = $css;
    }
    
    /**
     * Add a JavaScript file
     * 
     * Searches theme hierarchy: custom > default > base
     * 
     * @param string $filename JavaScript filename
     * @param string $area Optional area (admin, client, reseller)
     * @param bool $defer Whether to add defer attribute
     * @param bool $async Whether to add async attribute
     * @return void
     */
    public function addJs(string $filename, string $area = '', bool $defer = true, bool $async = false): void
    {
        $file = $this->findAssetFile('js', $filename, $area);
        
        if ($file) {
            $this->jsFiles[$filename] = [
                'path' => $file,
                'defer' => $defer,
                'async' => $async
            ];
        }
    }
    
    /**
     * Add inline JavaScript
     * 
     * @param string $js JavaScript content
     * @param string $id Optional identifier for uniqueness
     * @return void
     */
    public function addJsInline(string $js, string $id = ''): void
    {
        $this->jsInline[$id ?: count($this->jsInline)] = $js;
    }
    
    /**
     * Find an asset file in theme hierarchy
     * 
     * Searches: custom > current theme > default > base
     * 
     * @param string $type Asset type (css, js, etc.)
     * @param string $filename Filename to search for
     * @param string $area Optional area subdirectory
     * @return string|null Path to asset file or null if not found
     */
    protected function findAssetFile(string $type, string $filename, string $area = ''): ?string
    {
        $basePath = $this->config['themes_path'] ?? '';
        if (!$basePath) {
            return null;
        }
        
        $subdir = $area ? $type . DIRECTORY_SEPARATOR . $area : $type;
        $theme = $this->config['theme'] ?? 'default';
        
        // Search hierarchy
        $searchPaths = [
            $basePath . '/custom/assets/' . $subdir . '/' . $filename,
            $basePath . '/' . $theme . '/assets/' . $subdir . '/' . $filename,
            $basePath . '/default/assets/' . $subdir . '/' . $filename,
            $basePath . '/base/assets/' . $subdir . '/' . $filename,
        ];
        
        foreach ($searchPaths as $path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Get all CSS file tags for HTML head
     * 
     * @return string HTML string with <link> tags
     */
    public function getCssHeadTags(): string
    {
        $html = '';
        
        // Add CSS files first
        foreach ($this->cssFiles as $filename => $path) {
            $url = $this->getAssetUrl($path);
            $html .= sprintf(
                '<link rel="stylesheet" href="%s" data-asset="%s">%s',
                htmlspecialchars($url),
                htmlspecialchars($filename),
                PHP_EOL
            );
        }
        
        // Add inline CSS
        if (!empty($this->cssInline)) {
            $html .= '<style type="text/css">' . PHP_EOL;
            foreach ($this->cssInline as $id => $css) {
                $html .= '/* ' . htmlspecialchars($id) . ' */' . PHP_EOL;
                $html .= $css . PHP_EOL;
            }
            $html .= '</style>' . PHP_EOL;
        }
        
        return $html;
    }
    
    /**
     * Get all JavaScript file tags for HTML (before closing body)
     * 
     * @return string HTML string with <script> tags
     */
    public function getJsBodyTags(): string
    {
        $html = '';
        
        // Add JS files
        foreach ($this->jsFiles as $filename => $meta) {
            $deferAttr = $meta['defer'] ? ' defer' : '';
            $asyncAttr = $meta['async'] ? ' async' : '';
            $url = $this->getAssetUrl($meta['path']);
            
            $html .= sprintf(
                '<script src="%s" data-asset="%s"%s%s></script>%s',
                htmlspecialchars($url),
                htmlspecialchars($filename),
                $deferAttr,
                $asyncAttr,
                PHP_EOL
            );
        }
        
        // Add inline JS
        if (!empty($this->jsInline)) {
            $html .= '<script type="text/javascript">' . PHP_EOL;
            foreach ($this->jsInline as $id => $js) {
                $html .= '/* ' . htmlspecialchars($id) . ' */' . PHP_EOL;
                $html .= $js . PHP_EOL;
            }
            $html .= '</script>' . PHP_EOL;
        }
        
        return $html;
    }
    
    /**
     * Convert file path to URL for browser loading
     * 
     * @param string $filePath File path
     * @return string URL relative to theme or absolute URL
     */
    protected function getAssetUrl(string $filePath): string
    {
        // For now, return relative path from themes
        // In production, would be more sophisticated URL handling
        $themesPath = rtrim($this->config['themes_path'] ?? '/themes', '/');
        
        // Make relative URL
        if (strpos($filePath, $themesPath) === 0) {
            return substr($filePath, strlen(dirname($themesPath)));
        }
        
        return $filePath;
    }
    
    /**
     * Add DataBuilder default assets for admin area
     * 
     * @return void
     */
    public function addDefaultAdminAssets(): void
    {
        // Add base DataBuilder CSS
        $this->addCss('databuilder.css', '');
        
        // Add area-specific CSS if exists
        $this->addCss('admin.css', 'admin');
        
        // Add base DataBuilder JS
        $this->addJs('databuilder.js', '', true, false);
        
        // Add area-specific JS if exists
        $this->addJs('admin.js', 'admin', true, false);
    }
    
    /**
     * Clear all assets
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->cssFiles = [];
        $this->cssInline = [];
        $this->jsFiles = [];
        $this->jsInline = [];
    }
    
    /**
     * Get asset count
     * 
     * @return int Total number of assets registered
     */
    public function count(): int
    {
        return count($this->cssFiles) + count($this->cssInline) 
             + count($this->jsFiles) + count($this->jsInline);
    }
}
