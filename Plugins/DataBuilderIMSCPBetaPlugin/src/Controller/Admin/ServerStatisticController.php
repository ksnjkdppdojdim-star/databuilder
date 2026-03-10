<?php
/**
 * DataBuilder — Admin\ServerStatisticController
 *
 * Gère la page /admin/server_statistic.php d'i-MSCP.
 * Lit les données directement depuis le namespace iMSCP (via Registry 'imscp_tpl_data')
 * et les injecte dans les blocs définis par themes/custom/layouts/admin/server_statistic.xml.
 *
 * @author        Jules MAHOUNOU <jtodjinou@datatechnologies.bj>
 * @copyright (C) 2024 Jules MAHOUNOU
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

namespace DataBuilder\Controller\Admin;

use DataBuilder\Block\ContainerBlock;
use DataBuilder\Block\BlockFactory;
use DataBuilder\Controller\AbstractController;
use DataBuilder\Core\Registry;
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Router\Route;
use DataBuilder\Template\TemplateEngine;

class ServerStatisticController extends AbstractController
{
    public function __construct(
        Route         $route,
        Registry      $registry,
        LayoutManager $layoutManager,
        BlockFactory  $blockFactory,
        TemplateEngine $templateEngine
    ) {
        parent::__construct($route, $registry, $layoutManager, $blockFactory, $templateEngine);
    }

    public function execute(): string
    {
        $data      = $this->loadImscpData();
        $rootBlock = $this->loadLayout(); // handle = 'admin/server_statistic' via Route
        $this->propagateData($rootBlock, $data);
        return $rootBlock->render();
    }

    private function loadImscpData(): array
    {
        $tpl = $this->registry->get('imscp_tpl_data') ?: [];
        $v = function (string $key, string $default = '') use ($tpl): string {
            return (isset($tpl[$key]) && $tpl[$key] !== '') ? (string)$tpl[$key] : $default;
        };

        return [
            // Labels traduits par iMSCP
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

            // Selects pré-rendus par iMSCP parse()
            'day_list'   => $v('DAY_LIST',   ''),
            'month_list' => $v('MONTH_LIST', ''),
            'year_list'  => $v('YEAR_LIST',  ''),

            // Totaux trafic
            'WEB_IN_ALL'    => $v('WEB_IN_ALL',    '0 B'),
            'WEB_OUT_ALL'   => $v('WEB_OUT_ALL',   '0 B'),
            'SMTP_IN_ALL'   => $v('SMTP_IN_ALL',   '0 B'),
            'SMTP_OUT_ALL'  => $v('SMTP_OUT_ALL',  '0 B'),
            'POP_IN_ALL'    => $v('POP_IN_ALL',    '0 B'),
            'POP_OUT_ALL'   => $v('POP_OUT_ALL',   '0 B'),
            'OTHER_IN_ALL'  => $v('OTHER_IN_ALL',  '0 B'),
            'OTHER_OUT_ALL' => $v('OTHER_OUT_ALL', '0 B'),
            'ALL_IN_ALL'    => $v('ALL_IN_ALL',    '0 B'),
            'ALL_OUT_ALL'   => $v('ALL_OUT_ALL',   '0 B'),
            'ALL_ALL'       => $v('ALL_ALL',       '0 B'),

            // Lignes <tr> pré-rendus par iMSCP parse() en boucle
            'SERVER_STATS_ROWS' => $v('SERVER_STATS_DAY', ''),

            // Infos système (sans DB)
            'os'          => php_uname('s'),
            'kernel'      => php_uname('r'),
            'hostname'    => gethostname() ?: 'localhost',
            'php_version' => PHP_VERSION,
        ];
    }

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
