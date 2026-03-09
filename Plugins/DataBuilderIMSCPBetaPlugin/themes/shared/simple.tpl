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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-container { width: 100%; max-width: 400px; }
        .login-header { 
            background: white;
            padding: 40px 30px 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .login-header h1 { font-size: 28px; color: #333; margin-bottom: 10px; }
        .login-header p { color: #666; font-size: 14px; }
        .notice { 
            margin: 15px 30px;
            padding: 12px;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }
        .notice.success { background: #e6f7e6; color: #009900; border: 1px solid #99dd99; display: block; }
        .notice.error { background: #ffe6e6; color: #cc0000; border: 1px solid #ff9999; display: block; }
        .notice.warning { background: #fff9e6; color: #cc8800; border: 1px solid #ffdd99; display: block; }
        .notice.info { background: #e7f3ff; color: #004499; border: 1px solid #b3d9ff; display: block; }
        .login-content {
            background: white;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .login-footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- BDP: header_block -->
        <div class="login-header">
            <h1>i-MSCP</h1>
            <p>Internet Multi Server Control Panel</p>
        </div>
        <!-- EDP: header_block -->

        <!-- BDP: page_message -->
        <div id="notice" class="notice {MESSAGE_CLS}">
            {MESSAGE}
        </div>
        <!-- EDP: page_message -->

        <!-- BDP: page_content -->
        <div class="login-content">
            {LAYOUT_CONTENT}
        </div>
        <!-- EDP: page_content -->

        <!-- BDP: footer_block -->
        <div class="login-footer">
            <p>&copy; 2010-2026 i-MSCP Team. All rights reserved.</p>
        </div>
        <!-- EDP: footer_block -->
    </div>

    <script src="{THEME_ASSETS_PATH}/js/jquery/jquery.js?v={THEME_ASSETS_VERSION}"></script>
    <script src="{THEME_ASSETS_PATH}/js/imscp.min.js?v={THEME_ASSETS_VERSION}"></script>
</body>
</html>
