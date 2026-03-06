<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <title>{TR_PAGE_TITLE}</title>
    <meta charset="{THEME_CHARSET}">
    <meta name="robots" content="nofollow, noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{THEME_ASSETS_PATH}/images/favicon.ico">
    <link rel="stylesheet" href="{THEME_ASSETS_PATH}/css/theme.css?v={THEME_ASSETS_VERSION}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }
        .layout { display: flex; height: 100vh; }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }
        .sidebar-header h2 { font-size: 18px; color: #333; }
        .sidebar-nav {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
        }
        .sidebar-nav ul { list-style: none; }
        .sidebar-nav li { margin: 5px 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .sidebar-nav a:hover { background: #f0f0f0; }
        .sidebar-nav a.active { background: #e3f2fd; color: #1976d2; font-weight: 600; }
        .sidebar-nav .nav-icon { font-size: 20px; }
        .sidebar-nav .nav-label { flex: 1; }
        
        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .header h1 { font-size: 24px; color: #333; }
        
        /* Content area */
        .content-wrapper {
            flex: 1;
            overflow-y: auto;
            padding: 20px 30px;
        }
        
        /* Page content */
        .page-content {
            background: white;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        /* Messages */
        .notice { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .notice.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .notice.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .notice.warning { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
        .notice.info { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
        
        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e0e0e0;
            padding: 15px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- BDP: sidebar_block -->
        <aside class="sidebar">
            <!-- BDP: sidebar_header -->
            <div class="sidebar-header">
                <h2>i-MSCP</h2>
            </div>
            <!-- EDP: sidebar_header -->
            
            <!-- BDP: sidebar_nav -->
            <nav class="sidebar-nav">
                <?= theme_databuilder_buildSidebarMenu($this->navigation); ?>
            </nav>
            <!-- EDP: sidebar_nav -->
        </aside>
        <!-- EDP: sidebar_block -->

        <div class="main-content">
            <!-- BDP: header_block -->
            <header class="header">
                <h1>Welcome to i-MSCP</h1>
                <div>{PROFILE_LINK}</div>
            </header>
            <!-- EDP: header_block -->

            <!-- BDP: content_wrapper -->
            <div class="content-wrapper">
                <!-- BDP: page_message -->
                <!-- IF '{MESSAGE}' ne contient pas vide -->
                <div id="notice" class="notice {MESSAGE_CLS}">
                    {MESSAGE}
                </div>
                <!-- ENDIF -->
                <!-- EDP: page_message -->

                <!-- BDP: page_content -->
                <div class="page-content">
                    {LAYOUT_CONTENT}
                </div>
                <!-- EDP: page_content -->
            </div>
            <!-- EDP: content_wrapper -->

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

