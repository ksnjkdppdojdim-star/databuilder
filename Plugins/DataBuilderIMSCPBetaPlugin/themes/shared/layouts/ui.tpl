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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .layout { display: flex; height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: white; border-right: 1px solid #ddd; overflow-y: auto; padding: 20px; }
        .sidebar h2 { margin-bottom: 20px; font-size: 18px; }
        .sidebar ul { list-style: none; }
        .sidebar li { margin: 10px 0; }
        .sidebar li.active > a { color: #1976d2; font-weight: bold; background: #e3f2fd; }
        .sidebar a { color: #0066cc; text-decoration: none; display: block; padding: 8px 12px; border-radius: 4px; }
        .sidebar a:hover { background: #f0f0f0; }
        .sidebar > ul > li > ul { display: none; margin-top: 8px; padding-left: 10px; font-size: 14px; }
        .sidebar li.active > ul { display: block; }
        .sidebar > ul > li > ul > li { margin: 5px 0; }
        .sidebar > ul > li > ul > li a { padding: 5px 8px; }
        
        /* Main content */
        .main-content { flex: 1; display: flex; flex-direction: column; }
        
        /* Header */
        .header { background: white; border-bottom: 1px solid #ddd; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .header h1 { font-size: 20px; color: #333; margin: 0; }
        
        /* Submenu breadcrumb */
        .subheader { background: white; border-bottom: 1px solid #ddd; padding: 10px 30px; }
        .subheader ul { list-style: none; display: flex; gap: 20px; flex-wrap: wrap; }
        .subheader li { margin: 0; }
        .subheader a { color: #0066cc; text-decoration: none; padding: 8px 0; display: block; font-size: 14px; }
        .subheader a:hover { border-bottom: 2px solid #0066cc; }
        .subheader li.active a { color: #1976d2; border-bottom: 2px solid #1976d2; font-weight: bold; }
        
        /* Content */
        .content-wrapper { flex: 1; overflow-y: auto; padding: 20px 30px; }
        .page-content { background: white; padding: 20px; border: 1px solid #ddd; margin-bottom: 20px; }
        .notice { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .notice.success { background: #e6f7e6; color: #009900; border: 1px solid #99dd99; }
        .notice.error { background: #ffe6e6; color: #cc0000; border: 1px solid #ff9999; }
        .notice.warning { background: #fff9e6; color: #cc8800; border: 1px solid #ffdd99; }
        .notice.info { background: #e7f3ff; color: #004499; border: 1px solid #b3d9ff; }
        .footer { background: white; border-top: 1px solid #ddd; padding: 15px 30px; text-align: center; font-size: 12px; color: #666; }
    </style>
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

