<?php
/**
 * DataBuilder Theme - Entry Point for Admin Pages
 * 
 * This file bridges iMSCP theme system with DataBuilder Magento-like engine
 * It intercepts page requests and routes them to DataBuilder layouts/blocks/templates
 * 
 * Architecture: Magento-like with XML Layouts, PHP Blocks, PHTML Templates
 * No database queries - Pure HTTP variables and view rendering
 * 
 * Theme hierarchy: custom/ > default/ > base/
 * @author Jules MAHOUNOU
 */

// Get plugin directory
$pluginDir = dirname(__DIR__, 2);

// Initialize DataBuilder Engine
try {
    // Load configuration
    $config = [];
    $configFile = $pluginDir . '/config.php';
    if (file_exists($configFile)) {
        $config = include $configFile;
    }
    
    // Create engine instance
    $engine = \DataBuilder\Core\Engine::create([
        'base_path' => $pluginDir,
        'theme' => $config['theme'] ?? 'default',
        'debug' => $config['debug'] ?? false,
    ]);
    
    // Get current page from iMSCP
    $currentPage = basename($SCRIPT_NAME, '.php') ?? 'index';
    $area = strpos($SCRIPT_NAME, '/admin/') !== false ? 'admin' : 'client';
    
    // Render the page using DataBuilder
    echo $engine->renderPage($area, $currentPage);
    
} catch (Exception $e) {
    // Fallback to error display
    error_log("DataBuilder Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
     
    // Show user-friendly error
    echo "<div style='padding: 20px; background: #fee; color: #c00; border: 1px solid #c00; border-radius: 4px; margin: 20px;'>";
    echo "<h2>DataBuilder Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    if (($config['debug'] ?? false)) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    echo "</div>";
}

