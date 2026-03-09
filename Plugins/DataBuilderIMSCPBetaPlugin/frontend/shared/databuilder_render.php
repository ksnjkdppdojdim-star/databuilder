<?php
/**
 * DataBuilderIMSCPPlugin - Shared/Public Frontend Entry Point
 * 
 * This file is the entry point for the /databuilder public route
 * It works within the i-MSCP context and renders public pages
 */

// Get plugin directory
$pluginDir = dirname(__DIR__, 2);

// Simple response for the public route
// In a full implementation, this would dispatch to the DataBuilder engine
echo '<div class="container">';
echo '<h1>DataBuilder</h1>';
echo '<p>Public DataBuilder interface - Version 1.0.0</p>';
echo '<p>Theme: ' . htmlspecialchars($config['theme'] ?? 'base') . '</p>';
echo '</div>';

