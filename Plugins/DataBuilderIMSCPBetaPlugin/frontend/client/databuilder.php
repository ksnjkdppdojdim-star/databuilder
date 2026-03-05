<?php
/**
 * DataBuilderIMSCPPlugin - Client Frontend Entry Point
 * 
 * This file is the entry point for the /client/databuilder route
 * It works within the i-MSCP context and renders the client interface
 */

// Get plugin directory
$pluginDir = dirname(__DIR__, 2);

// Include the client page template
$templateFile = $pluginDir . '/themes/default/view/client/page.phtml';

if (!file_exists($templateFile)) {
    // Fallback to base theme if default theme template doesn't exist
    $templateFile = $pluginDir . '/themes/base/template/page/1column.phtml';
}

if (file_exists($templateFile)) {
    // Make plugin config available to the template
    $config = [];
    $configFile = $pluginDir . '/config.php';
    if (file_exists($configFile)) {
        $config = include $configFile;
    }
    
    // Set template variables
    $theme = $config['theme'] ?? 'base';
    $databuilderVersion = '1.0.0';
    
    // Include the template
    include $templateFile;
} else {
    echo '<div class="alert alert-danger">';
    echo '<h4>DataBuilder Error</h4>';
    echo '<p>Template file not found. Please reinstall the plugin.</p>';
    echo '</div>';
}

