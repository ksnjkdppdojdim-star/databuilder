<?php
/**
 * DataBuilder Direct Test - Standalone test executable
 * Access directly at: /plugins/DataBuilderIMSCPBetaPlugin/frontend/admin/direct-test.php
 */

// Force plain text output and bypass any iMSCP error handling
header('Content-Type: text/plain; charset=utf-8');

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

echo "=== DataBuilder Magento-lite Architecture Test ===\n\n";

// Get paths
$testFile = __FILE__;
$testDir = dirname($testFile);
$adminDir = dirname($testDir);
$frontendDir = dirname($adminDir);
$pluginDir = dirname($frontendDir);

echo "File: " . $testFile . "\n";
echo "Plugin Dir: " . $pluginDir . "\n\n";

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
    use DataBuilder\Core\Registry;
    use DataBuilder\Layout\LayoutManager;
    
    echo "Creating Registry...\n";
    $registry = new Registry();
    echo "✓ Registry created successfully\n";
    
    echo "\nCreating LayoutManager...\n";
    $config = [
        'themes_path' => $pluginDir . '/themes',
        'theme' => 'databuilder',
        'modules_path' => $pluginDir . '/modules',
    ];
    
    $layoutManager = new LayoutManager($config, $registry);
    echo "✓ LayoutManager created successfully\n";
    
    echo "\nLoading layout 'admin_test'...\n";
    $layoutData = $layoutManager->getLayout('admin_test');
    echo "✓ Layout loaded\n";
    echo "  Blocks count: " . count($layoutData) . "\n";
    echo "  Root block exists: " . (isset($layoutData['root']) ? "YES" : "NO") . "\n";
    
    if (isset($layoutData['root'])) {
        echo "\nRendering content...\n";
        $html = $layoutData['root']->render();
        echo "✓ Content rendered (" . strlen($html) . " bytes)\n";
        
        echo "\n=== SUCCESS ===\n";
        echo "Magento-lite architecture is WORKING!\n";
        echo "\nRendered HTML output:\n";
        echo str_repeat("-", 60) . "\n";
        echo $html;
        echo "\n" . str_repeat("-", 60) . "\n";
    }
    
} catch (Throwable $e) {
    echo "✗ EXCEPTION\n\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
