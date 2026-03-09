<?php
/**
 * DataBuilder iMSCP Bridge
 * 
 * Provides access to iMSCP variables and template data from within DataBuilder
 * This allows DataBuilder blocks to access iMSCP-defined variables without direct DB access
 */

namespace DataBuilder\Integration;

class ImscpBridge
{
    /**
     * Static cache of iMSCP variables
     * @var array
     */
    protected static $variables = [];
    
    /**
     * iMSCP template engine instance
     * @var \iMSCP\TemplateEngine|null
     */
    protected static $templateEngine = null;
    
    /**
     * Get iMSCP variable value
     * 
     * Retrieves variables that were assigned to iMSCP's template engine
     * Variables like {ADMIN_USERS}, {DOMAINS}, {TRAFFIC_PERCENT}, etc.
     * 
     * @param string $varName Variable name (e.g., 'ADMIN_USERS')
     * @param mixed $default Default value if not found
     * @return mixed Variable value
     */
    public static function getVariable(string $varName, $default = null)
    {
        // Try cache first
        if (isset(self::$variables[$varName])) {
            return self::$variables[$varName];
        }
        
        // Try to get from iMSCP Registry
        try {
            // Check if iMSCP registry has the value
            if (function_exists('iMSCP_Registry')) {
                $registry = \iMSCP_Registry::getInstance();
                
                // Try to get template engine
                if (!self::$templateEngine && $registry->isRegistered('TemplateEngine')) {
                    self::$templateEngine = $registry->get('TemplateEngine');
                }
                
                // If template engine available, try to get assignment
                if (self::$templateEngine) {
                    try {
                        $value = self::$templateEngine->getAssignment($varName);
                        if ($value !== null) {
                            self::$variables[$varName] = $value;
                            return $value;
                        }
                    } catch (\Exception $e) {
                        // continue to try other methods
                    }
                }
                
                // Try direct registry with key
                if ($registry->isRegistered($varName)) {
                    $value = $registry->get($varName);
                    self::$variables[$varName] = $value;
                    return $value;
                }
            }
        } catch (\Exception $e) {
            // Log but continue
            error_log("ImscpBridge: Error accessing variable: " . $e->getMessage());
        }
        
        // Return default if not found
        return $default;
    }
    
    /**
     * Get multiple iMSCP variables at once
     * 
     * @param array $varNames Array of variable names
     * @return array Array of [name => value] pairs
     */
    public static function getVariables(array $varNames): array
    {
        $result = [];
        foreach ($varNames as $name) {
            $result[$name] = self::getVariable($name);
        }
        return $result;
    }
    
    /**
     * Check if variable exists in iMSCP
     * 
     * @param string $varName Variable name
     * @return bool True if variable exists
     */
    public static function hasVariable(string $varName): bool
    {
        return self::getVariable($varName) !== null;
    }
    
    /**
     * Set/store a variable (cache)
     * 
     * Used for caching iMSCP variables to avoid repeated lookups
     * 
     * @param string $varName Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public static function setVariable(string $varName, $value): void
    {
        self::$variables[$varName] = $value;
    }
    
    /**
     * Inject iMSCP variables into DataBuilder registry
     * 
     * This is called from the plugin to cache all iMSCP template variables
     * so they're available to DataBuilder blocks
     * 
     * @param array $variables Array of [name => value] pairs from iMSCP template
     * @return void
     */
    public static function injectVariables(array $variables): void
    {
        self::$variables = array_merge(self::$variables, $variables);
    }
    
    /**
     * Clear variable cache
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        self::$variables = [];
        self::$templateEngine = null;
    }
    
    /**
     * Get all cached variables
     * 
     * @return array
     */
    public static function getAllVariables(): array
    {
        return self::$variables;
    }
}
