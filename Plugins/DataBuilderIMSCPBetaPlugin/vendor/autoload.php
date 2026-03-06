<?php
/**
 * i-MSCP DataBuilderIMSCPPlugin - Autoloader
 *
 * This autoloader loads DataBuilder classes using PSR-4 namespace convention.
 * It first checks local directories, then falls back to parent databuilder folder.
 *
 * @author        Your Name <your@email.com>
 * @copyright (C) 2024 Your Name
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

// Define base paths
define('DATABUILDER_PLUGIN_PATH', __DIR__ . '/../');
define('DATABUILDER_PARENT_PATH', dirname(__DIR__, 2) . '/'); // Go up to databuilder/

/**
 * Get the DataBuilder base path (plugin or parent)
 * 
 * @return string
 */
function getDataBuilderBasePath(): string
{
    // Check if local src exists, otherwise use parent
    if (is_dir(DATABUILDER_PLUGIN_PATH . 'src')) {
        return DATABUILDER_PLUGIN_PATH;
    }
    return DATABUILDER_PARENT_PATH;
}

/**
 * DataBuilder Autoloader
 */
spl_autoload_register(function ($class) {
    $prefix = 'DataBuilder\\';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    
    // Try local src first, then parent
    $localSrc = DATABUILDER_PLUGIN_PATH . 'src/';
    $parentSrc = DATABUILDER_PARENT_PATH . 'src/';
    
    $file = $localSrc . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (!file_exists($file)) {
        $file = $parentSrc . str_replace('\\', '/', $relativeClass) . '.php';
    }
    
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Helper function to get the DataBuilder path
 * 
 * @return string
 */
function getDataBuilderPath(): string
{
    return getDataBuilderBasePath();
}

/**
 * Helper function to get the DataBuilder src path
 * 
 * @return string
 */
function getDataBuilderSrcPath(): string
{
    $base = getDataBuilderBasePath();
    return $base . 'src/';
}

/**
 * Helper function to get the DataBuilder config path
 * 
 * @return string
 */
function getDataBuilderConfigPath(): string
{
    $base = getDataBuilderBasePath();
    // Try local config first
    if (is_dir($base . 'config')) {
        return $base . 'config/';
    }
    return DATABUILDER_PARENT_PATH . 'config/';
}

/**
 * Helper function to get the DataBuilder themes path
 * 
 * @return string
 */
function getDataBuilderThemesPath(): string
{
    $base = getDataBuilderBasePath();
    // Try local themes first
    if (is_dir($base . 'themes')) {
        return $base . 'themes/';
    }
    return DATABUILDER_PARENT_PATH . 'themes/';
}

/**
 * Helper function to get the DataBuilder modules path
 * 
 * @return string
 */
function getDataBuilderModulesPath(): string
{
    $base = getDataBuilderBasePath();
    // Try local modules first
    if (is_dir($base . 'modules')) {
        return $base . 'modules/';
    }
    return DATABUILDER_PARENT_PATH . 'modules/';
}

/**
 * Helper function to get the DataBuilder tenants path
 * 
 * @return string
 */
function getDataBuilderTenantsPath(): string
{
    $base = getDataBuilderBasePath();
    // Try local tenants first
    if (is_dir($base . 'tenants')) {
        return $base . 'tenants/';
    }
    return DATABUILDER_PARENT_PATH . 'tenants/';
}
