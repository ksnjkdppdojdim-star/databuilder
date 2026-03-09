<?php
/**
 * DataBuilder Server Statistic Controller
 * 
 * Contrôleur DataBuilder pour la page Server Statistics d'i-MSCP
 * Charge les données statistiques du serveur et les rend via le système de layout DataBuilder
 *
 * @author        Jules MAHOUNOU <jtodjinou@datatechnologies.bj>
 * @copyright (C) 2024 Jules MAHOUNOU
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

namespace DataBuilder\Controller;

use DataBuilder\Core\Registry;
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Block\BlockFactory;
use DataBuilder\Block\ContainerBlock;
use DataBuilder\Template\TemplateEngine;
use DataBuilder\Router\Route;

/**
 * Class ServerStatisticController
 * 
 * Controller pour la page des statistiques serveur
 * Étape 1: Charge les données i-MSCP (stats serveur)
 * Étape 2: Crée le layout DataBuilder
 * Étape 3: Crée les blocs avec les données
 * Étape 4: Rend le tout
 */
class ServerStatisticController extends AbstractController
{
    /**
     * Constructor
     * 
     * @param Route $route
     * @param Registry $registry
     * @param LayoutManager $layoutManager
     * @param BlockFactory $blockFactory
     * @param TemplateEngine $templateEngine
     */
    public function __construct(
        Route $route,
        Registry $registry,
        LayoutManager $layoutManager,
        BlockFactory $blockFactory,
        TemplateEngine $templateEngine
    ) {
        parent::__construct($route, $registry, $layoutManager, $blockFactory, $templateEngine);
    }

    /**
     * Execute the controller
     * 
     * Main entry point that:
     * 1. Loads i-MSCP server statistics data
     * 2. Creates the DataBuilder layout
     * 3. Builds blocks with data
     * 4. Renders everything
     * 
     * @return string Rendered HTML output
     */
    public function execute(): string
    {
        // Step 1: Load i-MSCP server statistics data
        $data = $this->loadImscpData();
        
        // Step 2: Create the DataBuilder layout
        $rootBlock = $this->loadLayout('server_statistic');
        
        // Step 3: Build blocks with data
        $this->populateBlocks($rootBlock, $data);
        
        // Step 4: Render everything
        return $rootBlock->render();
    }

    /**
     * Load i-MSCP server statistics data
     * 
     * Retrieves server traffic data from the i-MSCP database
     * 
     * @return array Server statistics data
     */
    private function loadImscpData(): array
    {
        $data = [
            'title' => tr('Server Statistics'),
            'TR_DAY' => tr('Day'),
            'TR_MONTH' => tr('Month'),
            'TR_YEAR' => tr('Year'),
            'TR_WEB_IN' => tr('Web In'),
            'TR_WEB_OUT' => tr('Web Out'),
            'TR_SMTP_IN' => tr('SMTP In'),
            'TR_SMTP_OUT' => tr('SMTP Out'),
            'TR_POP_IN' => tr('POP In'),
            'TR_POP_OUT' => tr('POP Out'),
            'TR_OTHER_IN' => tr('Other In'),
            'TR_OTHER_OUT' => tr('Other Out'),
            'TR_ALL_IN' => tr('Total In'),
            'TR_ALL_OUT' => tr('Total Out'),
            'TR_ALL' => tr('Total'),
            'TR_HOUR' => tr('Hour'),
            'TR_MONTHLY_STATS' => tr('Monthly Statistics'),
            'TR_DAILY_STATS' => tr('Daily Statistics'),
        ];

        // Get date parameters from request (day, month, year)
        $day = isset($_POST['day']) ? (int)$_POST['day'] : date('j');
        $month = isset($_POST['month']) ? (int)$_POST['month'] : date('n');
        $year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');

        // Build date lists for filter
        $data['day_list'] = $this->buildDayList($day);
        $data['month_list'] = $this->buildMonthList($month);
        $data['year_list'] = $this->buildYearList($year);

        // Calculate date range
        $startDate = mktime(0, 0, 0, $month, 1, $year);
        $endDate = mktime(23, 59, 59, $month, date('t', $startDate), $year);

        // Get server traffic data
        $trafficData = $this->getServerTraffic($startDate, $endDate, 0);
        
        $data['WEB_IN_ALL'] = $this->formatBytes($trafficData['web_in']);
        $data['WEB_OUT_ALL'] = $this->formatBytes($trafficData['web_out']);
        $data['SMTP_IN_ALL'] = $this->formatBytes($trafficData['smtp_in']);
        $data['SMTP_OUT_ALL'] = $this->formatBytes($trafficData['smtp_out']);
        $data['POP_IN_ALL'] = $this->formatBytes($trafficData['pop_in']);
        $data['POP_OUT_ALL'] = $this->formatBytes($trafficData['pop_out']);
        $data['OTHER_IN_ALL'] = $this->formatBytes($trafficData['other_in']);
        $data['OTHER_OUT_ALL'] = $this->formatBytes($trafficData['other_out']);
        $data['ALL_IN_ALL'] = $this->formatBytes($trafficData['all_in']);
        $data['ALL_OUT_ALL'] = $this->formatBytes($trafficData['all_out']);
        $data['ALL_ALL'] = $this->formatBytes($trafficData['all_in'] + $trafficData['all_out']);

        // Get system info
        $data['os'] = php_uname('s');
        $data['kernel'] = php_uname('r');
        $data['hostname'] = gethostname() ?: 'localhost';
        $data['php_version'] = PHP_VERSION;

        // Get monthly stats for table
        $data['server_stats_by_month'] = $this->getMonthlyStats($startDate, $endDate);

        return $data;
    }

    /**
     * Get server traffic data from database
     * 
     * @param int $startDate Start timestamp
     * @param int $endDate End timestamp
     * @param int $serverId Server ID (0 for all)
     * @return array Traffic data
     */
    private function getServerTraffic(int $startDate, int $endDate, int $serverId = 0): array
    {
        $default = [
            'web_in' => 0, 'web_out' => 0,
            'smtp_in' => 0, 'smtp_out' => 0,
            'pop_in' => 0, 'pop_out' => 0,
            'other_in' => 0, 'other_out' => 0,
            'all_in' => 0, 'all_out' => 0
        ];

        // Check if exec_query function exists (i-MSCP context)
        if (!function_exists('exec_query')) {
            return $default;
        }

        try {
            $stmt = exec_query(
                'SELECT 
                    IFNULL(SUM(bytes_web_in), 0) AS swbin,
                    IFNULL(SUM(bytes_web_out), 0) AS swbout,
                    IFNULL(SUM(bytes_mail_in), 0) AS smbin,
                    IFNULL(SUM(bytes_mail_out), 0) AS smbout,
                    IFNULL(SUM(bytes_pop_in), 0) AS spbin,
                    IFNULL(SUM(bytes_pop_out), 0) AS spbout,
                    IFNULL(SUM(bytes_in), 0) AS sbin,
                    IFNULL(SUM(bytes_out), 0) AS sbout
                FROM server_traffic
                WHERE server_id = ? AND traff_time BETWEEN ? AND ?',
                [$serverId, $startDate, $endDate]
            );

            if (!$stmt || !$stmt->rowCount()) {
                return $default;
            }

            $row = $stmt->fetchRow(\PDO::FETCH_ASSOC);
            
            $webIn = $row['swbin'] ?? 0;
            $webOut = $row['swbout'] ?? 0;
            $smtpIn = $row['smbin'] ?? 0;
            $smtpOut = $row['smbout'] ?? 0;
            $popIn = $row['spbin'] ?? 0;
            $popOut = $row['spbout'] ?? 0;
            $allIn = $row['sbin'] ?? 0;
            $allOut = $row['sbout'] ?? 0;

            return [
                'web_in' => $webIn,
                'web_out' => $webOut,
                'smtp_in' => $smtpIn,
                'smtp_out' => $smtpOut,
                'pop_in' => $popIn,
                'pop_out' => $popOut,
                'other_in' => $allIn - ($webIn + $smtpIn + $popIn),
                'other_out' => $allOut - ($webOut + $smtpOut + $popOut),
                'all_in' => $allIn,
                'all_out' => $allOut
            ];
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get monthly statistics
     * 
     * @param int $startDate Start timestamp
     * @param int $endDate End timestamp
     * @return array Monthly stats
     */
    private function getMonthlyStats(int $startDate, int $endDate): array
    {
        // Check if exec_query function exists
        if (!function_exists('exec_query')) {
            return [];
        }

        try {
            $stmt = exec_query(
                'SELECT 
                    FROM_UNIXTIME(traff_time, "%Y-%m-%d") AS traffic_date,
                    IFNULL(SUM(bytes_web_in), 0) AS swbin,
                    IFNULL(SUM(bytes_web_out), 0) AS swbout,
                    IFNULL(SUM(bytes_mail_in), 0) AS smbin,
                    IFNULL(SUM(bytes_mail_out), 0) AS smbout,
                    IFNULL(SUM(bytes_pop_in), 0) AS spbin,
                    IFNULL(SUM(bytes_pop_out), 0) AS spbout,
                    IFNULL(SUM(bytes_in), 0) AS sbin,
                    IFNULL(SUM(bytes_out), 0) AS sbout
                FROM server_traffic
                WHERE server_id = 0 AND traff_time BETWEEN ? AND ?
                GROUP BY traffic_date
                ORDER BY traffic_date ASC',
                [$startDate, $endDate]
            );

            if (!$stmt || !$stmt->rowCount()) {
                return [];
            }

            $stats = [];
            while ($row = $stmt->fetchRow(\PDO::FETCH_ASSOC)) {
                $stats[] = [
                    'date' => $row['traffic_date'],
                    'web_in' => $this->formatBytes($row['swbin']),
                    'web_out' => $this->formatBytes($row['swbout']),
                    'smtp_in' => $this->formatBytes($row['smbin']),
                    'smtp_out' => $this->formatBytes($row['smbout']),
                    'pop_in' => $this->formatBytes($row['spbin']),
                    'pop_out' => $this->formatBytes($row['spbout']),
                    'other_in' => $this->formatBytes($row['sbin'] - ($row['swbin'] + $row['smbin'] + $row['spbin'])),
                    'other_out' => $this->formatBytes($row['sbout'] - ($row['swbout'] + $row['smbout'] + $row['spbout'])),
                    'all_in' => $this->formatBytes($row['sbin']),
                    'all_out' => $this->formatBytes($row['sbout']),
                    'total' => $this->formatBytes($row['sbin'] + $row['sbout'])
                ];
            }

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Build day dropdown options
     * 
     * @param int $selected Selected day
     * @return string HTML options
     */
    private function buildDayList(int $selected): string
    {
        $html = '';
        for ($i = 1; $i <= 31; $i++) {
            $selectedAttr = ($i === $selected) ? ' selected="selected"' : '';
            $html .= '<option value="' . $i . '"' . $selectedAttr . '>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        return $html;
    }

    /**
     * Build month dropdown options
     * 
     * @param int $selected Selected month
     * @return string HTML options
     */
    private function buildMonthList(int $selected): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        $html = '';
        foreach ($months as $num => $name) {
            $selectedAttr = ($num === $selected) ? ' selected="selected"' : '';
            $html .= '<option value="' . $num . '"' . $selectedAttr . '>' . tr($name) . '</option>';
        }
        return $html;
    }

    /**
     * Build year dropdown options
     * 
     * @param int $selected Selected year
     * @return string HTML options
     */
    private function buildYearList(int $selected): string
    {
        $currentYear = (int)date('Y');
        $html = '';
        for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
            $selectedAttr = ($i === $selected) ? ' selected="selected"' : '';
            $html .= '<option value="' . $i . '"' . $selectedAttr . '>' . $i . '</option>';
        }
        return $html;
    }

    /**
     * Format bytes to human readable string
     * 
     * @param int|float $bytes Bytes to format
     * @return string Formatted string
     */
    private function formatBytes($bytes): string
    {
        $bytes = max(0, (float)$bytes);
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Populate blocks with data
     * 
     * @param ContainerBlock $rootBlock Root block
     * @param array $data Data to populate
     * @return void
     */
    private function populateBlocks(ContainerBlock $rootBlock, array $data): void
    {
        // Set data on root block (will be accessible to all children)
        foreach ($data as $key => $value) {
            $rootBlock->setData($key, $value);
        }

        // If using nested blocks, populate them specifically
        $content = $rootBlock->getChild('content');
        if ($content instanceof ContainerBlock) {
            // Get stats table block
            $statsTable = $content->getChild('stats_table');
            if ($statsTable instanceof ContainerBlock) {
                // Additional stats table specific data can be set here
            }
        }
    }
}

