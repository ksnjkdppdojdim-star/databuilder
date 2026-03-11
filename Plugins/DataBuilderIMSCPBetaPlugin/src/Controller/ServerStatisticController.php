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
     * Load i-MSCP server statistics data.
     *
     * DataBuilder does NOT access the database directly.
     * All traffic data is read from the iMSCP TemplateEngine's already-processed
     * variable store (dtplData) that was populated by the original iMSCP page script
     * before the DataBuilder plugin fires.
     *
     * Non-DB system info (OS, kernel, hostname, PHP version) is read from PHP
     * built-ins — no database dependency whatsoever.
     *
     * @return array Data for block templates
     */
    private function loadImscpData(): array
    {
        // Retrieve the snapshot that executeDataBuilderController extracted via Reflection.
        $tpl = $this->registry->get('imscp_tpl_data') ?: [];

        // Helper: get a value from iMSCP dtplData, falling back to $default.
        $v = function ($key, $default = '') use ($tpl) {
            return (isset($tpl[$key]) && $tpl[$key] !== '') ? $tpl[$key] : $default;
        };

        return [
            // ── Translated labels (iMSCP already ran tr()) ──────────────────────
            'TR_DAY'           => $v('TR_DAY',       'Day'),
            'TR_MONTH'         => $v('TR_MONTH',     'Month'),
            'TR_YEAR'          => $v('TR_YEAR',      'Year'),
            'TR_HOUR'          => $v('TR_HOUR',      'Hour'),
            'TR_WEB_IN'        => $v('TR_WEB_IN',    'Web in'),
            'TR_WEB_OUT'       => $v('TR_WEB_OUT',   'Web out'),
            'TR_SMTP_IN'       => $v('TR_SMTP_IN',   'SMTP in'),
            'TR_SMTP_OUT'      => $v('TR_SMTP_OUT',  'SMTP out'),
            'TR_POP_IN'        => $v('TR_POP_IN',    'POP3/IMAP in'),
            'TR_POP_OUT'       => $v('TR_POP_OUT',   'POP3/IMAP out'),
            'TR_OTHER_IN'      => $v('TR_OTHER_IN',  'Other in'),
            'TR_OTHER_OUT'     => $v('TR_OTHER_OUT', 'Other out'),
            'TR_ALL_IN'        => $v('TR_ALL_IN',    'All in'),
            'TR_ALL_OUT'       => $v('TR_ALL_OUT',   'All out'),
            'TR_ALL'           => $v('TR_ALL',       'All'),
            'TR_MONTHLY_STATS' => 'Monthly Statistics',
            'TR_DAILY_STATS'   => 'Daily Statistics',

            // ── Filter dropdowns (pre-rendered <option> HTML by iMSCP parse()) ─
            // iMSCP key is uppercase (DAY_LIST) ; templates expect lowercase keys.
            'day_list'   => $v('DAY_LIST',   ''),
            'month_list' => $v('MONTH_LIST', ''),
            'year_list'  => $v('YEAR_LIST',  ''),

            // ── Traffic totals (formatted strings assigned by iMSCP) ────────────
            'WEB_IN_ALL'   => $v('WEB_IN_ALL',   '0 B'),
            'WEB_OUT_ALL'  => $v('WEB_OUT_ALL',  '0 B'),
            'SMTP_IN_ALL'  => $v('SMTP_IN_ALL',  '0 B'),
            'SMTP_OUT_ALL' => $v('SMTP_OUT_ALL', '0 B'),
            'POP_IN_ALL'   => $v('POP_IN_ALL',   '0 B'),
            'POP_OUT_ALL'  => $v('POP_OUT_ALL',  '0 B'),
            'OTHER_IN_ALL' => $v('OTHER_IN_ALL', '0 B'),
            'OTHER_OUT_ALL'=> $v('OTHER_OUT_ALL','0 B'),
            'ALL_IN_ALL'   => $v('ALL_IN_ALL',   '0 B'),
            'ALL_OUT_ALL'  => $v('ALL_OUT_ALL',  '0 B'),
            'ALL_ALL'      => $v('ALL_ALL',      '0 B'),

            // ── Per-day rows: pre-rendered <tr> HTML from iMSCP parse() loops ──
            // SERVER_STATS_DAY is the accumulated HTML from repeated parse() calls.
            'SERVER_STATS_ROWS' => $v('SERVER_STATS_DAY', ''),

            // ── System info (PHP built-ins, zero DB access) ─────────────────────
            'os'          => php_uname('s'),
            'kernel'      => php_uname('r'),
            'hostname'    => gethostname() ?: 'localhost',
            'php_version' => PHP_VERSION,
        ];
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
        // Propagate all data to the root block and every descendant so that
        // each block's .phtml template can read $block->getData(...).
        $this->propagateData($rootBlock, $data);
    }

    /**
     * Recursively assigns $data to a block and all its descendants.
     */
    private function propagateData(\DataBuilder\Block\BlockInterface $block, array $data): void
    {
        if ($block instanceof \DataBuilder\Block\AbstractBlock) {
            $block->assignData($data);
        }
        foreach ($block->getChildren() as $child) {
            $this->propagateData($child, $data);
        }
    }
}

