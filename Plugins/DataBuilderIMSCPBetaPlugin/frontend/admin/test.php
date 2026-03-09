<?php
/**
 * DataBuilder Test Page - Standalone test
 * Access at: http://server:8880/databuilder/admin/test.php
 */

// Force plain text output and bypass any iMSCP error handling
header('Content-Type: text/plain; charset=utf-8');

// Enable ALL error reporting - show everything
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Disable output buffering to ensure data flows through
if (ob_get_level()) {
    ob_end_clean();
}

echo "=== DataBuilder Magento-lite Architecture Test ===\n\n";

// Get paths - from public/databuilder/admin/ go up to gui/
$currentDir = dirname(__FILE__);   // /var/www/imscp/gui/public/databuilder/admin
$databuilderPublicDir = dirname($currentDir);  // /var/www/imscp/gui/public/databuilder
$publicDir = dirname($databuilderPublicDir);   // /var/www/imscp/gui/public
$guiDir = dirname($publicDir);     // /var/www/imscp/gui
$pluginDir = $guiDir . '/plugins/DataBuilderIMSCPBetaPlugin';

echo "Current Dir: " . $currentDir . "\n";
echo "GUI Dir: " . $guiDir . "\n";
echo "Plugin Dir: " . $pluginDir . "\n\n";

if (!is_dir($pluginDir)) {
    echo "ERROR: Plugin directory not found at " . $pluginDir . "\n";
    exit(1);
}

// Load autoloader
$autoloaderPath = $pluginDir . '/vendor/autoload.php';
echo "Autoloader: " . $autoloaderPath . "\n";

if (!file_exists($autoloaderPath)) {
    echo "ERROR: Autoloader not found!\n";
    exit(1);
}

require_once $autoloaderPath;
echo "Status: Autoloader loaded\n\n";

// Now test the system
echo "=== Testing Classes ===\n";

$classes = [
    'DataBuilder\Core\Registry',
    'DataBuilder\Layout\LayoutManager',
    'DataBuilder\Block\AbstractBlock',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✓ " . $class . "\n";
    } else {
        echo "✗ " . $class . " NOT FOUND\n";
    }
}

echo "\n=== Testing Instantiation ===\n";

try {
    echo "Creating Registry...\n";
    $registry = new \DataBuilder\Core\Registry();
    echo "✓ Registry created successfully\n";
    
    echo "\nCreating LayoutManager...\n";
    $config = [
        'themes_path' => $guiDir . '/themes',
        'theme' => 'databuilder',
        'modules_path' => $pluginDir . '/modules',
    ];
    
    $layoutManager = new \DataBuilder\Layout\LayoutManager($config, $registry);
    echo "✓ LayoutManager created successfully\n";
    
    echo "\nLoading layout 'admin_test'...\n";
    $layoutData = $layoutManager->getLayout('admin_test');
    echo "✓ Layout XML loaded\n";
    
    echo "\nBuilding block tree...\n";
    $blockBuilder = new \DataBuilder\Layout\BlockBuilder($registry);
    $rootBlock = $blockBuilder->build($layoutData);
    
    if (!$rootBlock) {
        echo "✗ Failed to build blocks from layout\n";
        exit(1);
    }
    
    echo "✓ Block tree built successfully\n";
    
    // Initialise le TemplateEngine
    echo "\nInitializing TemplateEngine...\n";
    $cacheDir = $guiDir . '/data/cache/databuilder-templates';
    $templateConfig = [
        'themes_path' => $config['themes_path'],
        'theme' => $config['theme'],
        'modules_path' => $config['modules_path'],
        'cache_path' => $cacheDir,
        'cache_enable' => true,
        'debug' => false,
    ];
    
    // Crée le répertoire cache s'il n'existe pas
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $templateEngine = new \DataBuilder\Template\TemplateEngine($templateConfig);
    $rootBlock->setTemplateEngine($templateEngine);
    echo "✓ TemplateEngine configured\n";
    
    echo "\nRendering content...\n";
    
    // Debug: Check if template files exist
    echo "Template files check:\n";
    $templatesDir = $guiDir . '/themes/databuilder/templates/test';
    if (is_dir($templatesDir)) {
        $files = scandir($templatesDir);
        echo "  Found " . (count($files) - 2) . " template files in " . $templatesDir . "\n";
        foreach ($files as $f) {
            if ($f !== '.' && $f !== '..') {
                echo "    - " . $f . "\n";
            }
        }
    } else {
        echo "  ERROR: Template directory not found at " . $templatesDir . "\n";
    }
    
    // Now try to render with full error catching
    try {
        echo "Calling render()...\n";
        $html = @$rootBlock->render(); // Suppress notices too
        echo "Render returned " . strlen($html) . " bytes\n";
        
        if (empty($html)) {
            echo "ERROR: render() returned empty string!\n";
            echo "Checking if root block has template: " . (method_exists($rootBlock, 'getTemplate') ? "YES" : "NO") . "\n";
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION during render:\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        $html = "<!-- EXCEPTION: " . $e->getMessage() . " -->";
    }
    
    echo "✓ Content rendered (" . strlen($html) . " bytes)\n";
    
    echo "\n=== SUCCESS ===\n";
    echo "Magento-lite architecture is WORKING!\n";
    echo "\nRendered HTML output:\n";
    echo str_repeat("-", 60) . "\n";
    echo $html;
    echo "\n" . str_repeat("-", 60) . "\n";
    
} catch (Throwable $e) {
    echo "✗ EXCEPTION\n\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
