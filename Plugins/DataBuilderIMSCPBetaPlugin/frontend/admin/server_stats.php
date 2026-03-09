<?php
// Use statements MUST be at the top!
use DataBuilder\Core\Registry;
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Layout\BlockBuilder;
use DataBuilder\Template\TemplateEngine;

try {
    // Get paths
    $currentDir = dirname(__FILE__);
    $databuilderPublicDir = dirname($currentDir);
    $publicDir = dirname($databuilderPublicDir);
    $guiDir = dirname($publicDir);
    $pluginDir = $guiDir . '/plugins/DataBuilderIMSCPBetaPlugin';

    $autoloaderPath = $pluginDir . '/vendor/autoload.php';
    
    if (!file_exists($autoloaderPath)) {
        throw new Exception('Autoloader not found');
    }
    
    require_once $autoloaderPath;

    // Initialize config array
    $config = [
        'gui_path'          => $guiDir,
        'plugin_path'       => $pluginDir,
        'theme'             => 'databuilder',
        'themes_path'       => $guiDir . '/themes',
        'modules_path'      => $pluginDir . '/modules',
        'cache_path'        => $guiDir . '/data/cache/databuilder-templates',
        'cache_enable'      => false,
    ];
    
    // Initialize registry (no constructor arguments)
    $registry = new Registry();
    
    // Store config in registry
    $registry->set('config', $config);

    // Load and build layout
    $layoutManager = new LayoutManager($config, $registry);
    
    $layoutData = $layoutManager->getLayout('admin_server_stats');

    $blockBuilder = new BlockBuilder($registry);
    
    $rootBlock = $blockBuilder->build($layoutData);

    if (!$rootBlock) {
        throw new Exception('Failed to build block tree');
    }

    // Setup template engine
    $templateConfig = [
        'themes_path'  => $guiDir . '/themes',
        'theme'        => 'databuilder',
        'modules_path' => $pluginDir . '/modules',
        'cache_path'   => $guiDir . '/data/cache/databuilder-templates',
        'cache_enable' => false,
    ];
    
    $templateEngine = new TemplateEngine($templateConfig);
    
    $rootBlock->setTemplateEngine($templateEngine);

    // Populate data - Use iMSCP's approach
    $rootBlock->setData('title', 'Server Statistics');
    
    // Helper function to get server traffic (from iMSCP)
    $getServerTraffic = function($startDate, $endDate, $serverId) {
        $stmt = exec_query(
            'SELECT IFNULL(SUM(bytes_in), 0) AS sbin,
                IFNULL(SUM(bytes_out), 0) AS sbout,
                IFNULL(SUM(bytes_mail_in), 0) AS smbin,
                IFNULL(SUM(bytes_mail_out), 0) AS smbout,
                IFNULL(SUM(bytes_pop_in), 0) AS spbin,
                IFNULL(SUM(bytes_pop_out), 0) AS spbout,
                IFNULL(SUM(bytes_web_in), 0) AS swbin,
                IFNULL(SUM(bytes_web_out), 0) AS swbout
            FROM server_traffic
            WHERE server_id=? AND traff_time BETWEEN ? AND ?',
            [$serverId, $startDate, $endDate]
        );

        if (!$stmt->rowCount()) {
            return array_fill(0, 10, 0);
        }

        $row = $stmt->fetchRow(PDO::FETCH_ASSOC);
        return [
            $row['swbin'], $row['swbout'], $row['smbin'], $row['smbout'], $row['spbin'], $row['spbout'],
            $row['sbin'] - ($row['swbin'] + $row['smbin'] + $row['spbin']),
            $row['sbout'] - ($row['swbout'] + $row['smbout'] + $row['spbout']),
            $row['sbin'], $row['sbout']
        ];
    };
    
    // Get today's traffic data
    $serverId = 0;
    $startDate = mktime(0, 0, 0);
    $endDate = mktime(23, 59, 59);
    
    list($webIn, $webOut, $smtpIn, $smtpOut, $popIn, $popOut, $otherIn, $otherOut, $allIn, $allOut) = 
        $getServerTraffic($startDate, $endDate, $serverId);
    
    // Access content block through main_wrapper
    $mainWrapper = $rootBlock->getChild('main_wrapper');
    $content = $mainWrapper ? $mainWrapper->getChild('content') : null;
    
    if ($content) {
        $statsOverview = $content->getChild('stats_overview');
        
        if ($statsOverview) {
            // Use bytesHuman if available, otherwise format manually
            $formatBytes = function_exists('bytesHuman') ? 'bytesHuman' : function($bytes) {
                $units = ['B', 'KB', 'MB', 'GB'];
                $bytes = max($bytes, 0);
                if ($bytes == 0) return '0 B';
                $pow = floor(log($bytes, 1024));
                $pow = min($pow, count($units) - 1);
                $bytes /= pow(1024, $pow);
                return round($bytes, 2) . ' ' . $units[$pow];
            };
            
            $statsOverview->setData('WEB_IN_ALL', $formatBytes($webIn));
            $statsOverview->setData('WEB_OUT_ALL', $formatBytes($webOut));
            $statsOverview->setData('SMTP_IN_ALL', $formatBytes($smtpIn));
            $statsOverview->setData('SMTP_OUT_ALL', $formatBytes($smtpOut));
            $statsOverview->setData('POP_IN_ALL', $formatBytes($popIn));
            $statsOverview->setData('POP_OUT_ALL', $formatBytes($popOut));
            $statsOverview->setData('OTHER_IN_ALL', $formatBytes($otherIn));
            $statsOverview->setData('OTHER_OUT_ALL', $formatBytes($otherOut));
            $statsOverview->setData('ALL_IN_ALL', $formatBytes($allIn));
            $statsOverview->setData('ALL_OUT_ALL', $formatBytes($allOut));
            $statsOverview->setData('ALL_ALL', $formatBytes($allIn + $allOut));
        }
        
        $statsDetails = $content->getChild('stats_details');
        
        if ($statsDetails) {
            $statsDetails->setData('os', php_uname('s'));
            $statsDetails->setData('kernel', php_uname('r'));
            $statsDetails->setData('hostname', gethostname() ?: 'localhost');
            $statsDetails->setData('php_version', PHP_VERSION);
            $statsDetails->setData('mysql_version', 'Connected');
        }
    }

    // Render and output
    $output = $rootBlock->render();
    echo $output;

} catch (Throwable $e) {
    error_log('SERVER_STATS ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    
    echo '<div style="padding: 20px; background: #fee; border: 2px solid #c00; margin: 20px; border-radius: 4px; font-family: monospace; color: #600;">';
    echo '<h2>✗ EXCEPTION</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}


