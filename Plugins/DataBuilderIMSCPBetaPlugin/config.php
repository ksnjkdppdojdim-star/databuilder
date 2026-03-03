<?php
/**
 * i-MSCP DataBuilderIMSCPPlugin Configuration
 *
 * @author        Jules MAHOUNOU <jtodjinou@datatechnologies.bj>
 * @copyright (C) 2024 Jules MAHOUNOU
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

return [
    // Enable/disable the DataBuilder engine
    'enabled' => true,
    
    // Default theme to use
    'theme' => 'custom',
    
    // Enable caching
    'cache_enabled' => true,
    
    // Cache TTL in seconds
    'cache_ttl' => 3600,
    
    // Debug mode
    'debug' => false,
    
    // Enable DataBuilder for clients (yes/no)
    'enable_for_clients' => 'yes',
    
    // Enable DataBuilder for resellers (yes/no)
    'enable_for_resellers' => 'yes',
    
    // Template configuration
    'template_extension' => '.phtml',
    
    // Module configuration
    'modules_enable' => true,
    'modules_auto_load' => true,
    
    // Router configuration
    'router_default_controller' => 'index',
    'router_default_action' => 'index',
];
