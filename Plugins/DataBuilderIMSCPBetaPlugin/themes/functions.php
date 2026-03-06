<?php
/**
 * DataBuilder Theme Functions
 * 
 * Theme-specific helper functions for the DataBuilder theme
 * Based on i-MSCP default theme functions
 */

/**
 * Get the theme assets version for cache busting
 * 
 * @return string Version string
 */
function theme_databuilder_getAssetsVersion()
{
    // Increment this version when CSS/JS files change to force browser reload
    return '1.0.0.0001';
}

/**
 * Get Material Symbol icon based on CSS class or URL
 *
 * @param string $cssClass The CSS class of menu item
 * @param string $uri The link URL (for fallback)
 * @return string Material Symbol icon name
 */
function theme_databuilder_getIcon($cssClass, $uri = '')
{
    $icons = [
        'general'       => 'dashboard',
        'manage_users'  => 'groups',
        'users'         => 'groups',
        'customers'     => 'groups',
        'domains'       => 'language',
        'ftp'           => 'folder_open',
        'database'      => 'storage',
        'email'         => 'mail',
        'hosting_plans' => 'inventory_2',
        'statistics'    => 'monitoring',
        'webtools'      => 'build',
        'system_tools'  => 'build',
        'support'       => 'support_agent',
        'settings'      => 'settings',
        'profile'       => 'person',
        'webspaces'     => 'dns'
    ];

    $classes = explode(' ', $cssClass);
    foreach ($classes as $cls) {
        if (isset($icons[$cls])) {
            return $icons[$cls];
        }
    }

    // Fallback based on URL
    if (strpos($uri, '/settings') !== false) return 'settings';
    if (strpos($uri, '/profile') !== false) return 'person';
    if (strpos($uri, '/support') !== false || strpos($uri, '/ticket') !== false) return 'support_agent';
    if (strpos($uri, '/stat') !== false) return 'monitoring';

    return 'chevron_right';
}

/**
 * Build the sidebar navigation menu with icons
 *
 * @param Zend_Navigation $navigation The navigation object
 * @return string HTML menu markup
 */
function theme_databuilder_buildSidebarMenu($navigation)
{
    $container = $navigation->menu()->getContainer();
    $html = '<ul class="flex flex-col gap-1">';

    foreach ($container as $page) {
        // Check if user can access this page
        if (!$navigation->accept($page)) {
            continue;
        }

        $isActive = $page->isActive(true);
        $href = $page->getHref();
        $label = $page->getLabel();
        $target = $page->getTarget();
        $targetAttr = $target ? ' target="' . htmlspecialchars($target) . '"' : '';
        
        // Get icon for this menu item
        $iconName = theme_databuilder_getIcon($page->getClass(), $href);
        
        // Determine active state
        $activeClass = $isActive ? ' active' : '';
        $activeLinkClass = $isActive ? ' active' : '';

        $html .= '<li class="nav-item' . $activeClass . '">';
        $html .= '<a href="' . htmlspecialchars($href) . '" class="nav-link' . $activeLinkClass . '"' . $targetAttr . '>';
        
        // Icon
        $html .= '<span class="material-symbols-outlined nav-icon">' . htmlspecialchars($iconName) . '</span>';
        
        // Label
        $html .= '<span class="nav-label">' . htmlspecialchars($label) . '</span>';
        
        $html .= '</a>';
        $html .= '</li>';
    }

    $html .= '</ul>';
    return $html;
}

