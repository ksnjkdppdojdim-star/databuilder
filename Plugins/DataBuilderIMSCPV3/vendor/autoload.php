<?php
/**
 * Minimal autoloader for DataBuilderIMSCPPlugin
 * This enables the theme to load DataBuilder classes
 */

spl_autoload_register(function ($class) {
    $prefixes = [
        "DataBuilder\\" => __DIR__ . "/../src/",
    ];
    
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Helper function to check if DataBuilder has a page
function databuilder_has_page(string $page, string $area = 'admin'): bool
{
    $pluginDir = dirname(__DIR__, 2);
    
    if (!class_exists('DataBuilder\Theme\ImscpThemeFallback')) {
        return false;
    }
    
    $themeFallback = new \DataBuilder\Theme\ImscpThemeFallback([], $pluginDir);
    return $themeFallback->hasDataBuilderPage($page, $area);
}

// Helper function to get DataBuilder content
function databuilder_get_content(string $page, string $area = 'admin', array $externalData = []): string
{
    $pluginDir = dirname(__DIR__, 2);
    
    if (!class_exists('DataBuilder\Theme\ImscpThemeFallback')) {
        return '';
    }
    
    $themeFallback = new \DataBuilder\Theme\ImscpThemeFallback([], $pluginDir);
    
    if (!$themeFallback->hasDataBuilderPage($page, $area)) {
        return '';
    }
    
    $templatePath = $themeFallback->resolve($page, $area);
    
    if (!$templatePath || !file_exists($templatePath)) {
        return '';
    }
    
    // Load page data from XML
    $dataXmlPath = $pluginDir . '/themes/default/data/' . $area . '/' . $page . 'Data.xml';
    $pageData = [];
    
    if (file_exists($dataXmlPath)) {
        $xml = simplexml_load_file($dataXmlPath);
        if ($xml && isset($xml->variables->variable)) {
            foreach ($xml->variables->variable as $var) {
                $name = (string)$var['name'];
                $value = (string)$var;
                $type = (string)($var['type'] ?? 'string');
                
                if ($type === 'integer') {
                    $pageData[$name] = (int)$value;
                } else {
                    $pageData[$name] = $value;
                }
            }
        }
    }
    
    // Merge with external data (from iMSCP)
    if (!empty($externalData)) {
        $pageData = array_merge($pageData, $externalData);
    }
    
    // Render the template
    extract($pageData);
    
    ob_start();
    include $templatePath;
    return ob_get_clean();
}

