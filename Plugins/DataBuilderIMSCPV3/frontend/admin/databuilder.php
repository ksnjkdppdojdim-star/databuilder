<?php
/**
 * DataBuilderIMSCPPlugin - Admin Frontend Entry Point
 * 
 * This file is the entry point for the /admin/databuilder route
 * Works standalone but uses iMSCP-compatible styling
 */

// Get plugin directory
$pluginDir = dirname(__DIR__, 2);

// Load plugin config
$config = [];
$configFile = $pluginDir . '/config.php';
if (file_exists($configFile)) {
    $config = include $configFile;
}

// Settings
$theme = $config['theme'] ?? 'default';
$cacheEnabled = $config['cache_enabled'] ?? false;
$debug = $config['debug'] ?? false;
$enableClients = $config['enable_for_clients'] ?? 'yes';
$enableResellers = $config['enable_for_resellers'] ?? 'yes';

// Handle form submission
if (isset($_POST['save_settings'])) {
    $newConfig = [
        'enabled' => true,
        'theme' => $_POST['theme'] ?? 'default',
        'cache_enabled' => isset($_POST['cache_enabled']),
        'debug' => isset($_POST['debug']),
        'enable_for_clients' => isset($_POST['enable_for_clients']) ? 'yes' : 'no',
        'enable_for_resellers' => isset($_POST['enable_for_resellers']) ? 'yes' : 'no',
        'cache_ttl' => 3600,
        'template_extension' => '.phtml',
        'modules_enable' => true,
        'modules_auto_load' => true,
        'router_default_controller' => 'index',
        'router_default_action' => 'index',
    ];
    
    $configContent = "<?php\n/**\n * i-MSCP DataBuilderIMSCPPlugin Configuration\n */\n\nreturn " . var_export($newConfig, true) . ";\n";
    file_put_contents($pluginDir . '/config.php', $configContent);
    
    // Show success message (will be displayed on next load)
    $saved = true;
    
    // Update local variables
    $theme = $newConfig['theme'];
    $cacheEnabled = $newConfig['cache_enabled'];
    $debug = $newConfig['debug'];
    $enableClients = $newConfig['enable_for_clients'];
    $enableResellers = $newConfig['enable_for_resellers'];
}

// Theme selection values
$themeBaseSelected = ($theme === 'base') ? ' selected' : '';
$themeDefaultSelected = ($theme === 'default') ? ' selected' : '';
$themeCustomSelected = ($theme === 'custom') ? ' selected' : '';

// Checkbox values
$cacheChecked = $cacheEnabled ? ' checked' : '';
$debugChecked = $debug ? ' checked' : '';
$clientsChecked = ($enableClients === 'yes') ? ' checked' : '';
$resellersChecked = ($enableResellers === 'yes') ? ' checked' : '';

// Status text
$cacheStatus = $cacheEnabled ? 'Enabled' : 'Disabled';
$clientsStatus = ($enableClients === 'yes') ? 'Enabled' : 'Disabled';
$resellersStatus = ($enableResellers === 'yes') ? 'Enabled' : 'Disabled';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DataBuilder Settings - i-MSCP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* iMSCP-compatible minimal styles */
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { margin-top: 0; }
        h3 {
            background: linear-gradient(to right, #4a90d9, #6ba3e0);
            color: white;
            padding: 12px 15px;
            margin: 0 0 15px 0;
            border-radius: 4px 4px 0 0;
        }
        .card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .card-body { padding: 20px; }
        
        /* Form styles */
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; margin-bottom: 5px; font-weight: 500; }
        select {
            width: 100%;
            max-width: 300px;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="checkbox"] {
            margin-right: 8px;
        }
        .field-desc {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        
        /* Button */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4a90d9;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #3a7bc8; }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th { background: #f8f9fa; font-weight: 600; }
        
        /* Links */
        a { color: #4a90d9; text-decoration: none; }
        a:hover { text-decoration: underline; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
        
        /* Info box */
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        /* Success message */
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($saved)): ?>
        <div class="alert alert-success">
            Settings saved successfully.
        </div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>DataBuilder</strong><br>
            Magento-like templating system for i-MSCP. Provides flexible theming, module system, and layout management.
        </div>
        
        <div class="card">
            <h3>DataBuilder Settings</h3>
            <div class="card-body">
                <form method="post">
                    <div class="form-group">
                        <label for="theme">Theme</label><br>
                        <select name="theme" id="theme">
                            <option value="base"<?php echo $themeBaseSelected; ?>>Base Theme</option>
                            <option value="default"<?php echo $themeDefaultSelected; ?>>Default Theme</option>
                            <option value="custom"<?php echo $themeCustomSelected; ?>>Custom Theme</option>
                        </select>
                        <p class="field-desc">Select the default theme for the templating system</p>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="cache_enabled" id="cache_enabled"<?php echo $cacheChecked; ?> />
                            Enable cache
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="debug" id="debug"<?php echo $debugChecked; ?> />
                            Enable debug mode
                        </label>
                        <p class="field-desc">Enable debug mode for development - shows detailed error messages</p>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="enable_for_clients" id="enable_for_clients"<?php echo $clientsChecked; ?> />
                            Enable for clients
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="enable_for_resellers" id="enable_for_resellers"<?php echo $resellersChecked; ?> />
                            Enable for resellers
                        </label>
                    </div>
                    
                    <button type="submit" name="save_settings" class="btn">Save Settings</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <h3>DataBuilder Statistics</h3>
            <div class="card-body">
                <table>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>Theme</td>
                        <td><?php echo htmlspecialchars($theme); ?></td>
                    </tr>
                    <tr>
                        <td>Cache</td>
                        <td><?php echo htmlspecialchars($cacheStatus); ?></td>
                    </tr>
                    <tr>
                        <td>Clients</td>
                        <td><?php echo htmlspecialchars($clientsStatus); ?></td>
                    </tr>
                    <tr>
                        <td>Resellers</td>
                        <td><?php echo htmlspecialchars($resellersStatus); ?></td>
                    </tr>
                    <tr>
                        <td>PHP Version</td>
                        <td><?php echo htmlspecialchars(PHP_VERSION); ?></td>
                    </tr>
                    <tr>
                        <td>DataBuilder Version</td>
                        <td>1.0.0</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card">
            <h3>Test DataBuilder Pages</h3>
            <div class="card-body">
                <ul>
                    <li><a href="/databuilder/demo/public/index.php" target="_blank">Demo Homepage</a> - Full DataBuilder engine</li>
                    <li><a href="/client/databuilder" target="_blank">Client Page</a> - Client area test</li>
                    <li><a href="/admin/databuilder">Admin Settings</a> - This page</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>

