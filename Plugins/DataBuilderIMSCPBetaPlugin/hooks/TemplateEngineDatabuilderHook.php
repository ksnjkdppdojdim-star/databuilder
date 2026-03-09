<?php
/**
 * i-MSCP DataBuilder Plugin - TemplateEngine Hook
 * 
 * This hook extends the i-MSCP TemplateEngine to add getRootDir() method
 * which is needed for the DataBuilder template override system.
 * 
 * This file is automatically installed/removed by the DataBuilder plugin.
 * It can be safely deleted manually without affecting i-MSCP functionality.
 * 
 * @author        Jules MAHOUNOU <jtodjinou@datatechnologies.bj>
 * @copyright (C) 2024 Jules MAHOUNOU
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP;

/**
 * Class TemplateEngineDatabuilderHook
 * 
 * This class extends the i-MSCP TemplateEngine to provide getRootDir() method.
 * It is used ONLY when DataBuilder theme is active.
 * 
 * @package iMSCP
 */
class TemplateEngineDatabuilderHook extends TemplateEngine
{
    /**
     * Get templates root directory
     * 
     * Returns the root directory where templates are located.
     * This method is needed by the DataBuilder plugin to implement
     * Magento-like theme fallback system.
     * 
     * @return string The root directory path
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }
    
    /**
     * Check if a template override exists in DataBuilder themes
     * 
     * This method checks if there's a DataBuilder override for the given template.
     * 
     * @param string $templatePath The template path relative to rootDir
     * @return string|null The override path if exists, null otherwise
     */
    public function getDataBuilderOverridePath(string $templatePath): ?string
    {
        // Get the DataBuilder plugin themes path
        $pluginDir = dirname(__DIR__, 4) . '/plugins/DataBuilderIMSCPBetaPlugin';
        $themesDir = $pluginDir . '/themes';
        
        // Remove .tpl extension if present
        $templateName = str_replace('.tpl', '', $templatePath);
        
        // Check themes hierarchy: custom -> default -> base
        $themeHierarchy = ['custom', 'default', 'base'];
        
        foreach ($themeHierarchy as $theme) {
            $overridePath = $themesDir . '/' . $theme . '/admin/' . $templateName . '.phtml';
            
            if (file_exists($overridePath)) {
                return $overridePath;
            }
        }
        
        return null;
    }
}

