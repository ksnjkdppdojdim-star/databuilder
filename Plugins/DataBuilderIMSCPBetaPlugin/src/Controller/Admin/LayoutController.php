<?php
/**
 * DataBuilder — Admin\LayoutController
 *
 * Gère la page /admin/layout.php d'i-MSCP (profil › mise en page).
 * Permet à l'administrateur de choisir le thème, la couleur et les
 * paramètres d'affichage du panneau, ainsi que de gérer son logo.
 *
 * @author        Jules MAHOUNOU <jtodjinou@datatechnologies.bj>
 * @copyright (C) 2024 Jules MAHOUNOU
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

namespace DataBuilder\Controller\Admin;

use DataBuilder\Controller\AbstractController;
use DataBuilder\Block\BlockFactory;
use DataBuilder\Core\Registry;
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Router\Route;
use DataBuilder\Template\TemplateEngine;

class LayoutController extends AbstractController
{
    public function __construct(
        Route          $route,
        Registry       $registry,
        LayoutManager  $layoutManager,
        BlockFactory   $blockFactory,
        TemplateEngine $templateEngine
    ) {
        parent::__construct($route, $registry, $layoutManager, $blockFactory, $templateEngine);
    }

    public function execute(): string
    {
        $data      = $this->buildLayoutData();
        $rootBlock = $this->loadLayout(); // handle = 'admin/layout' via Route
        $this->propagateData($rootBlock, $data);
        return $rootBlock->render();
    }

    private function buildLayoutData(): array
    {
        // ── Thèmes disponibles ───────────────────────────────────────────
        $themes = function_exists('layout_getAvailableThemes')
            ? layout_getAvailableThemes()
            : [];

        // ── Préférences utilisateur actuelles ───────────────────────────
        $userId   = $_SESSION['user_id']   ?? 0;
        $userType = $_SESSION['user_type'] ?? 'admin';
        $isAdminOrReseller = in_array($userType, ['admin', 'reseller'], true);
        $isMyAccount       = !isset($_SESSION['logged_from_id']) && !isset($_SESSION['logged_from']);

        $userGuiProps  = function_exists('get_user_gui_props') ? get_user_gui_props($userId) : [];
        $selectedTheme = $userGuiProps['layout'] ?? ($_SESSION['user_theme'] ?? '');
        if (!in_array($selectedTheme, $themes, true) && !empty($themes)) {
            $selectedTheme = $themes[0];
        }

        $colors = function_exists('layout_getAvailableColorSet')
            ? (layout_getAvailableColorSet($selectedTheme) ?: ['_none_'])
            : ['_none_'];

        $selectedColor = $userGuiProps['layout_color'] ?? ($_SESSION['user_theme_color'] ?? '');
        if (!in_array($selectedColor, $colors, true)) {
            $selectedColor = $colors[0] ?? '_none_';
        }

        // ── JSON des thèmes → couleurs pour le JS ────────────────────────
        $themesData = [];
        foreach ($themes as $theme) {
            $tc = function_exists('layout_getAvailableColorSet')
                ? (layout_getAvailableColorSet($theme) ?: ['_none_'])
                : ['_none_'];
            $themesData[$theme] = array_combine($tc, $tc);
            if (isset($themesData[$theme]['_none_'])) {
                unset($themesData[$theme]['_none_']);
                $themesData[$theme]['default'] = '_none_';
            }
        }

        // ── Logo ─────────────────────────────────────────────────────────
        $ispLogo = '';
        $hasLogo = false;
        if ($isAdminOrReseller && function_exists('layout_getUserLogo')) {
            $ispLogo = (string) layout_getUserLogo($userType === 'admin');
            $hasLogo = function_exists('layout_isUserLogo') && layout_isUserLogo($ispLogo);
        }

        // ── Visibilité des labels du menu principal ───────────────────────
        $showLabels = false;
        if ($isMyAccount) {
            $showLabels = (bool) ($_SESSION['show_main_menu_labels'] ?? false);
        } elseif (function_exists('layout_isMainMenuLabelsVisible')) {
            $showLabels = (bool) layout_isMainMenuLabelsVisible($userId);
        }

        // ── Traductions depuis le namespace iMSCP ────────────────────────
        $d = $this->registry->get('imscp_tpl_data') ?: [];
        $v = function (string $key, string $default = '') use ($d): string {
            return (isset($d[$key]) && $d[$key] !== '') ? (string) $d[$key] : $default;
        };

        return [
            // Données thème
            'themes'           => $themes,
            'selected_theme'   => $selectedTheme,
            'colors'           => $colors,
            'selected_color'   => $selectedColor,
            'themes_data_json' => json_encode($themesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT),

            // Logo
            'is_admin_reseller' => $isAdminOrReseller,
            'isp_logo'          => $ispLogo,
            'has_logo'          => $hasLogo,

            // Paramètres d'affichage
            'show_labels_on'  => $showLabels ? ' checked' : '',
            'show_labels_off' => !$showLabels ? ' checked' : '',

            // Traductions
            'tr_layout'                => $v('TR_LAYOUT',                'Layout'),
            'tr_choose_layout'         => $v('TR_CHOOSE_LAYOUT',         'Choose layout'),
            'tr_choose_layout_color'   => $v('TR_CHOOSE_LAYOUT_COLOR',   'Choose layout color'),
            'tr_update'                => $v('TR_UPDATE',                'Update'),
            'tr_other_settings'        => $v('TR_OTHER_SETTINGS',        'Other settings'),
            'tr_main_menu_show_labels' => $v('TR_MAIN_MENU_SHOW_LABELS', 'Show labels for main menu links'),
            'tr_enabled'               => $v('TR_ENABLED',               'Enabled'),
            'tr_disabled'              => $v('TR_DISABLED',              'Disabled'),
            'tr_logo_file'             => $v('TR_LOGO_FILE',             'Logo file'),
            'tr_upload'                => $v('TR_UPLOAD',                'Upload'),
            'tr_remove'                => $v('TR_REMOVE',                'Remove'),
            'tr_page_title'            => $v('TR_PAGE_TITLE',            'Admin / Profile / Layout'),
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
