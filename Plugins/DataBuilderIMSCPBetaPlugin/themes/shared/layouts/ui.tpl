<?php
use iMSCP\TemplateEngine;
use iMSCP\Registry;

/** @var TemplateEngine $this */
$container = $this->navigation->getContainer();

// Find active page/menu for submenus display
$leftMenu = null;
foreach($container as $page) {
    if($page->isActive(true)) {
        $leftMenu = $page;
        break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{TR_PAGE_TITLE}</title>
    <link rel="stylesheet" href="{THEME_ASSETS_PATH}/css/theme.css?v={THEME_ASSETS_VERSION}">
    <link rel="stylesheet" href="{THEME_ASSETS_PATH}/css/theme-vars.css?v={THEME_ASSETS_VERSION}">
</head>
<body>
    <div class="layout">
        <!-- BDP: sidebar_block -->
        <aside class="sidebar">
            <h2>i-MSCP</h2>
            <!-- BDP: navigation_menu -->
            <?= $this->navigation->menu() ?>
            <!-- EDP: navigation_menu -->
        </aside>
        <!-- EDP: sidebar_block -->

        <div class="main-content">
            <!-- BDP: header_block -->
            <div class="header">
                <h1><?= $leftMenu ? htmlspecialchars($leftMenu->getLabel()) : 'Control Panel' ?></h1>
                <div></div>
            </div>
            <!-- EDP: header_block -->

            <!-- BDP: subheader_block -->
            <?php if($leftMenu): ?>
            <div class="subheader">
                <!-- BDP: submenu -->
                <?= $this->navigation->menu()->renderMenu($leftMenu, [
                    'ulClass'  => '',
                    'indent'   => 0,
                    'minDepth' => 0,
                    'maxDepth' => 0,
                    'renderParents' => false,
                ]) ?>
                <!-- EDP: submenu -->
            </div>
            <?php endif; ?>
            <!-- EDP: subheader_block -->

            <div class="content-wrapper">
                <!-- BDP: page_message -->
                <div id="notice" class="notice {MESSAGE_CLS}">
                    {MESSAGE}
                </div>
                <!-- EDP: page_message -->

                <!-- BDP: page_content -->
                <div class="page-content">
                    {LAYOUT_CONTENT}
                </div>
                <!-- EDP: page_content -->
            </div>

            <!-- BDP: footer_block -->
            <footer class="footer">
                <p>&copy; 2010-2026 i-MSCP. All rights reserved.</p>
            </footer>
            <!-- EDP: footer_block -->
        </div>
    </div>

    <script src="{THEME_ASSETS_PATH}/js/jquery/jquery.js?v={THEME_ASSETS_VERSION}"></script>
    <script src="{THEME_ASSETS_PATH}/js/imscp.min.js?v={THEME_ASSETS_VERSION}"></script>
</body>
</html>

