<?php
/**
 * DataBuilder Cleanup Manager
 * 
 * Handles complete cleanup of DataBuilder plugin installation
 * Removes all directories, files, and caches created during installation
 */

namespace DataBuilder\Cleanup;

class CleanupManager
{
    /**
     * Directories to remove during uninstallation
     * @var array
     */
    protected $directoriesToRemove = [
        // Plugin data directories
        '%PLUGIN_DIR%/cache',
        '%PLUGIN_DIR%/data',
        '%PLUGIN_DIR%/logs',
        '%PLUGIN_DIR%/tmp',
        
        // iMSCP theme directory
        '%IMSCP_THEMES%/databuilder',
    ];
    
    /**
     * Files to remove during uninstallation (relative to plugin dir)
     * @var array
     */
    protected $filesToRemove = [
        'cleanup_complete.sql',
    ];
    
    /**
     * Caches to clear
     * @var array
     */
    protected $cachesToClear = [
        '%PLUGIN_DIR%/cache',
        '%IMSCP_ROOT%/gui/cache',
    ];
    
    /**
     * Plugin root directory
     * @var string
     */
    protected $pluginDir;
    
    /**
     * iMSCP root directory
     * @var string
     */
    protected $imscpRoot;
    
    /**
     * Constructor
     * 
     * @param string $pluginDir Path to plugin directory
     * @param string $imscpRoot Path to iMSCP installation directory
     */
    public function __construct(string $pluginDir, string $imscpRoot)
    {
        $this->pluginDir = rtrim($pluginDir, '/\\');
        $this->imscpRoot = rtrim($imscpRoot, '/\\');
    }
    
    /**
     * Execute complete cleanup
     * 
     * Removes all DataBuilder-related files and directories
     * 
     * @return array Status report
     */
    public function cleanup(): array
    {
        $report = [
            'removed_directories' => [],
            'removed_files' => [],
            'cleared_caches' => [],
            'errors' => [],
        ];
        
        // Remove directories
        foreach ($this->directoriesToRemove as $dirPattern) {
            $dir = $this->resolvePath($dirPattern);
            if ($dir && is_dir($dir)) {
                try {
                    $this->removeDirectory($dir);
                    $report['removed_directories'][] = $dir;
                } catch (\Exception $e) {
                    $report['errors'][] = "Failed to remove directory: {$dir} - " . $e->getMessage();
                }
            }
        }
        
        // Remove individual files
        foreach ($this->filesToRemove as $filePattern) {
            $file = $this->resolvePath('%PLUGIN_DIR%/' . $filePattern);
            if ($file && is_file($file)) {
                try {
                    unlink($file);
                    $report['removed_files'][] = $file;
                } catch (\Exception $e) {
                    $report['errors'][] = "Failed to remove file: {$file} - " . $e->getMessage();
                }
            }
        }
        
        // Clear caches
        foreach ($this->cachesToClear as $cachePattern) {
            $cache = $this->resolvePath($cachePattern);
            if ($cache && is_dir($cache)) {
                try {
                    $this->clearCacheDirectory($cache);
                    $report['cleared_caches'][] = $cache;
                } catch (\Exception $e) {
                    $report['errors'][] = "Failed to clear cache: {$cache} - " . $e->getMessage();
                }
            }
        }
        
        return $report;
    }
    
    /**
     * Resolve path placeholders
     * 
     * @param string $pattern Path pattern with placeholders
     * @return string Resolved path
     */
    protected function resolvePath(string $pattern): string
    {
        return strtr($pattern, [
            '%PLUGIN_DIR%' => $this->pluginDir,
            '%IMSCP_ROOT%' => $this->imscpRoot,
            '%IMSCP_THEMES%' => $this->imscpRoot . '/gui/themes',
        ]);
    }
    
    /**
     * Remove directory recursively
     * 
     * @param string $dir Directory path
     * @throws \Exception
     */
    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $items = @scandir($dir);
        if ($items === false) {
            throw new \Exception("Failed to scan directory: {$dir}");
        }
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                if (!@unlink($path)) {
                    throw new \Exception("Failed to delete file: {$path}");
                }
            }
        }
        
        if (!@rmdir($dir)) {
            throw new \Exception("Failed to delete directory: {$dir}");
        }
    }
    
    /**
     * Clear all files from cache directory (keep directory structure)
     * 
     * @param string $dir Cache directory
     * @throws \Exception
     */
    protected function clearCacheDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $items = @scandir($dir);
        if ($items === false) {
            return; // Ignore if can't scan
        }
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (is_file($path)) {
                @unlink($path);
            } elseif (is_dir($path)) {
                $this->clearCacheDirectory($path);
                @rmdir($path); // Remove empty subdirectories
            }
        }
    }
    
    /**
     * Get cleanup report as HTML
     * 
     * @param array $report Cleanup report from cleanup()
     * @return string HTML formatted report
     */
    public static function getReportHtml(array $report): string
    {
        $html = '<div style="font-family: monospace; padding: 20px; background: #f5f5f5; border-radius: 5px;">';
        
        $html .= '<h3>DataBuilder Cleanup Report</h3>';
        
        if (!empty($report['removed_directories'])) {
            $html .= '<h4>Removed Directories:</h4><ul>';
            foreach ($report['removed_directories'] as $dir) {
                $html .= '<li>' . htmlspecialchars($dir) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($report['removed_files'])) {
            $html .= '<h4>Removed Files:</h4><ul>';
            foreach ($report['removed_files'] as $file) {
                $html .= '<li>' . htmlspecialchars($file) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($report['cleared_caches'])) {
            $html .= '<h4>Cleared Caches:</h4><ul>';
            foreach ($report['cleared_caches'] as $cache) {
                $html .= '<li>' . htmlspecialchars($cache) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($report['errors'])) {
            $html .= '<h4 style="color: red;">Errors:</h4><ul>';
            foreach ($report['errors'] as $error) {
                $html .= '<li style="color: red;">' . htmlspecialchars($error) . '</li>';
            }
            $html .= '</ul>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
