<?php
/**
 * i-MSCP DataBuilderIMSCPPlugin - Integration Adapter
 *
 * This adapter integrates DataBuilder with i-MSCP
 *
 * @author        Your Name <your@email.com>
 * @copyright (C) 2024 Your Name
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

namespace DataBuilder\Integration\Imscp;

use DataBuilder\Integration\IntegrationInterface;
use DataBuilder\Router\RouterInterface;

/**
 * Class ImscpAdapter
 * 
 * Adapter for i-MSCP integration
 */
class ImscpAdapter implements IntegrationInterface
{
    /**
     * @var string Base path for DataBuilder
     */
    protected $basePath;
    
    /**
     * @var string Active theme
     */
    protected $theme;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->basePath = defined('IMSCP_SYSTEM_UI_ROOT') 
            ? IMSCP_SYSTEM_UI_ROOT . '/plugins/DataBuilderIMSCPPlugin'
            : dirname(__DIR__, 3);
        
        $this->theme = $this->getActiveTheme();
    }
    
    /**
     * @inheritDoc
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @inheritDoc
     */
    public function getActiveTheme(): string
    {
        // Get theme from i-MSCP configuration or session
        if (defined('IMSCP_THEME')) {
            return IMSCP_THEME;
        }
        
        // Check for user preference
        if (isset($_SESSION['databuilder_theme'])) {
            return $_SESSION['databuilder_theme'];
        }
        
        return 'custom';
    }

    /**
     * @inheritDoc
     */
    public function getGlobalData(): array
    {
        return [
            'user'    => $_SESSION['user_logged'] ?? '',
            'user_type' => $_SESSION['user_type'] ?? '',
            'domain'  => $_SERVER['HTTP_HOST'] ?? '',
            'version' => defined('IMSCP_VERSION') ? IMSCP_VERSION : '',
            'theme'   => $this->getActiveTheme(),
            'base_url' => $this->getBaseUrl(),
        ];
    }
    
    /**
     * Get base URL
     * 
     * @return string
     */
    protected function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        return $protocol . '://' . $host . ($baseDir !== '/' ? $baseDir : '');
    }

    /**
     * @inheritDoc
     */
    public function getRouter(): ?RouterInterface
    {
        // Return null to use DataBuilder's built-in router
        // Or implement a custom router that integrates with i-MSCP's routing
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getTenantId(): ?string
    {
        // For multi-tenant hosting, return the tenant ID
        // This would typically come from the domain or user session
        if (isset($_SESSION['tenant_id'])) {
            return $_SESSION['tenant_id'];
        }
        
        return null;
    }
    
    /**
     * Check if current user is admin
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
    
    /**
     * Check if current user is reseller
     * 
     * @return bool
     */
    public function isReseller(): bool
    {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'reseller';
    }
    
    /**
     * Check if current user is client
     * 
     * @return bool
     */
    public function isClient(): bool
    {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'client';
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public function getUserId(): ?int
    {
        if (isset($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }
        
        return null;
    }
}
