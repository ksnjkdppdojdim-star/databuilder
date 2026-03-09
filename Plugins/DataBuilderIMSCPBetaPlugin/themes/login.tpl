
<?php
// DataBuilder Magento-lite Layout System - Login Page

error_log("\n=== LOGIN.TPL: START ===\n");

// FIRST: Load autoloader before any use statements
$autoloaderPath = __DIR__ . '/../../plugins/DataBuilderIMSCPBetaPlugin/vendor/autoload.php';
error_log("LOGIN.TPL: Checking autoloader at: " . $autoloaderPath);

if (!file_exists($autoloaderPath)) {
    error_log("LOGIN.TPL: ERROR - Autoloader not found!");
    ?>
    <div id="login">
        <p>Autoloader not found</p>
        <form name="login" action="/login.php" method="post">
            <table>
                <tr>
                    <td class="left"><label for="uname">{TR_USERNAME}</label></td>
                    <td class="right"><input type="text" name="uname" id="uname" value="{UNAME}"></td>
                </tr>
                <tr>
                    <td class="left"><label for="password">{TR_PASSWORD}</label></td>
                    <td class="right"><input type="password" name="upass" id="password" value=""></td>
                </tr>
                <tr>
                    <td colspan="2" class="right">
                        <a class="link_as_button" href="lostpassword.php">{TR_LOSTPW}</a>
                        <button type="submit" name="Submit" tabindex="3">{TR_LOGIN}</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
    exit;
}

error_log("LOGIN.TPL: Autoloader found, requiring...");
require_once $autoloaderPath;
error_log("LOGIN.TPL: Autoloader loaded successfully");

// NOW we can use namespaced classes
use DataBuilder\Layout\LayoutManager;
use DataBuilder\Core\Registry;

try {
    error_log("LOGIN.TPL: Creating Registry...");
    $registry = new Registry();
    error_log("LOGIN.TPL: Registry created");
    
    $themesPath = dirname(__DIR__);
    error_log("LOGIN.TPL: Themes path = " . $themesPath);
    
    $config = [
        'themes_path' => $themesPath,
        'theme' => 'databuilder',
        'modules_path' => dirname(dirname(__DIR__)) . '/modules',
    ];
    
    error_log("LOGIN.TPL: Creating LayoutManager with config: " . json_encode($config));
    $layoutManager = new LayoutManager($config, $registry);
    error_log("LOGIN.TPL: LayoutManager created");
    
    error_log("LOGIN.TPL: Loading 'user_login' layout...");
    $layoutData = $layoutManager->getLayout('user_login');
    error_log("LOGIN.TPL: Layout loaded, layoutData keys: " . implode(', ', array_keys($layoutData)));
    
    error_log("LOGIN.TPL: About to render root block");
    echo $layoutData['root']->render();
    error_log("LOGIN.TPL: Rendering complete");
    
} catch (\Throwable $e) {
    error_log("LOGIN.TPL: EXCEPTION CAUGHT!");
    error_log("LOGIN.TPL: Exception message: " . $e->getMessage());
    error_log("LOGIN.TPL: Exception file: " . $e->getFile());
    error_log("LOGIN.TPL: Exception line: " . $e->getLine());
    error_log("LOGIN.TPL: Exception trace: " . $e->getTraceAsString());
    
    // Fallback
    ?>
    <div id="login">
        <p>Error occurred (see logs)</p>
        <form name="login" action="/login.php" method="post">
            <table>
                <tr>
                    <td class="left"><label for="uname">{TR_USERNAME}</label></td>
                    <td class="right"><input type="text" name="uname" id="uname" value="{UNAME}"></td>
                </tr>
                <tr>
                    <td class="left"><label for="password">{TR_PASSWORD}</label></td>
                    <td class="right"><input type="password" name="upass" id="password" value=""></td>
                </tr>
                <tr>
                    <td colspan="2" class="right">
                        <a class="link_as_button" href="lostpassword.php">{TR_LOSTPW}</a>
                        <button type="submit" name="Submit" tabindex="3">{TR_LOGIN}</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}

error_log("LOGIN.TPL: END\n");
?>
