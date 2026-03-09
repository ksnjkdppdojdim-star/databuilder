<?php
/**
 * DataBuilder Admin Index Block
 *
 * Handles logic for the admin dashboard index page.
 * Loads admin statistics and traffic data from iMSCP.
 * 
 * Uses ImscpBridge to access iMSCP template variables without database queries.
 * This block can be extended in themes/custom/ directory without modifying core.
 */

namespace DataBuilder\Block\Admin;

use DataBuilder\Block\AbstractBlock;
use DataBuilder\Block\BlockInterface;
use DataBuilder\Integration\ImscpBridge;

class IndexBlock extends AbstractBlock implements BlockInterface
{
    /**
     * Statistics to display on admin dashboard
     * @var array
     */
    protected $statistics = [];
    
    /**
     * Traffic data
     * @var array
     */
    protected $traffic = [];
    
    /**
     * Initialize block data
     * 
     * Loads admin statistics from iMSCP variables using ImscpBridge.
     * Note: iMSCP variables are accessed without database queries.
     *
     * @return void
     */
    protected function _init()
    {
        // Load statistics from iMSCP variables via ImscpBridge
        $this->loadStatistics();
        
        // Load traffic data
        $this->loadTrafficData();
        
        // Set default template if not already set
        if (!$this->hasTemplate()) {
            $this->setTemplate('admin/index.phtml');
        }
    }
    
    /**
     * Load administrative statistics from iMSCP
     * 
     * Retrieves statistics from iMSCP template variables using ImscpBridge.
     * These variables are pre-calculated by iMSCP during the request.
     * NO DATABASE QUERIES ARE PERFORMED.
     *
     * @return void
     */
    protected function loadStatistics()
    {
        // List of statistics to load from iMSCP
        // Map: [key => iMSCP_variable_name]
        $statisticKeys = [
            'admin_users' => 'ADMIN_USERS',
            'reseller_users' => 'RESELLER_USERS', 
            'normal_users' => 'NORMAL_USERS',
            'domains' => 'DOMAINS',
            'ftp_accounts' => 'FTP_ACCOUNTS',
            'sql_databases' => 'SQL_DATABASES',
            'sql_users' => 'SQL_USERS',
            'mail_accounts' => 'MAIL_ACCOUNTS'
        ];
        
        // Load each statistic from iMSCP via ImscpBridge
        foreach ($statisticKeys as $key => $variable) {
            // Get value from iMSCP (using ImscpBridge)
            // ImscpBridge reads from iMSCP's template assignments
            $value = ImscpBridge::getVariable($variable, '0');
            
            $this->statistics[$key] = [
                'variable' => $variable,
                'value' => $value,
                'label_key' => 'TR_' . $variable  // iMSCP provides translated labels
            ];
        }
        
        // Assign to template
        $this->assign('statistics', $this->statistics);
    }
    
    /**
     * Load server traffic data from iMSCP
     * 
     * Retrieves traffic information from iMSCP template variables.
     * NO DATABASE QUERIES.
     *
     * @return void
     */
    protected function loadTrafficData()
    {
        // Load traffic information via ImscpBridge
        $this->traffic = [
            'percent' => ImscpBridge::getVariable('TRAFFIC_PERCENT', '0'),
            'percent_width' => ImscpBridge::getVariable('TRAFFIC_PERCENT_WIDTH', '0%'),
            'warning' => ImscpBridge::getVariable('TRAFFIC_WARNING', ''),
            'label' => 'TR_SERVER_TRAFFIC'  // iMSCP provides translation
        ];
        
        // Assign to template
        $this->assign('traffic', $this->traffic);
    }
    
    /**
     * Get all statistics
     * 
     * @return array
     */
    public function getStatistics()
    {
        return $this->statistics;
    }
    
    /**
     * Get traffic data
     * 
     * @return array
     */
    public function getTraffic()
    {
        return $this->traffic;
    }
    
    /**
     * Get a single statistic by key
     * 
     * @param string $key Statistic key
     * @return array|null
     */
    public function getStatistic($key)
    {
        return isset($this->statistics[$key]) ? $this->statistics[$key] : null;
    }
    
    /**
     * Get block name
     * 
     * @return string
     */
    public function getBlockName()
    {
        return 'AdminIndexBlock';
    }
    
    /**
     * Get block description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'Displays admin dashboard with statistics and traffic information';
    }
}
