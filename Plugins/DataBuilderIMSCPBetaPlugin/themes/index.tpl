<?php
/**
 * DataBuilder Theme - Admin Index Template
 * 
 * This is the main entry point for the admin area.
 * It loads the UI layout and injects the DataBuilder content.
 */

// Get the plugin directory
$pluginDir = dirname(__DIR__);

// Get the current page from iMSCP - use SCRIPT_NAME to identify the page
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Extract page name from script path (e.g., /admin/index.php -> index)
$currentPage = 'index'; // Default
if (!empty($scriptName)) {
    $pathInfo = pathinfo($scriptName);
    if (isset($pathInfo['filename'])) {
        $currentPage = strtolower($pathInfo['filename']);
    }
}

// Try to load DataBuilder rendered content
$dbContent = '';

/**
 * DataBuilder Helper Functions
 * These are included directly to avoid dependency on plugin installation
 */

// Simple autoloader for DataBuilder classes
spl_autoload_register(function ($class) use ($pluginDir) {
    $prefix = 'DataBuilder\\';
    $baseDir = $pluginDir . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});

// Check if DataBuilder has a page
function databuilder_has_page(string $page, string $area = 'admin'): bool
{
    global $pluginDir;
    
    if (!class_exists('DataBuilder\Theme\ImscpThemeFallback')) {
        return false;
    }
    
    $themeFallback = new \DataBuilder\Theme\ImscpThemeFallback([], $pluginDir);
    return $themeFallback->hasDataBuilderPage($page, $area);
}

// Get DataBuilder content
function databuilder_get_content(string $page, string $area = 'admin', array $externalData = []): string
{
    global $pluginDir;
    
    if (!class_exists('DataBuilder\Theme\ImscpThemeFallback')) {
        return '';
    }
    
    $themeFallback = new \DataBuilder\Theme\ImscpThemeFallback([], $pluginDir);
    
    if (!$themeFallback->hasDataBuilderPage($page, $area)) {
        return '';
    }
    
    $templatePath = $themeFallback->resolve($page, $area);
    
    if (!$templatePath || !file_exists($templatePath)) {
        return '';
    }
    
    // Load page data from XML
    $dataXmlPath = $pluginDir . '/themes/default/data/' . $area . '/' . $page . 'Data.xml';
    $pageData = [];
    
    if (file_exists($dataXmlPath)) {
        $xml = simplexml_load_file($dataXmlPath);
        if ($xml && isset($xml->variables->variable)) {
            foreach ($xml->variables->variable as $var) {
                $name = (string)$var['name'];
                $value = (string)$var;
                $type = (string)($var['type'] ?? 'string');
                
                if ($type === 'integer') {
                    $pageData[$name] = (int)$value;
                } else {
                    $pageData[$name] = $value;
                }
            }
        }
    }
    
    // Merge with external data (from iMSCP)
    if (!empty($externalData)) {
        $pageData = array_merge($pageData, $externalData);
    }
    
    // Render the template
    extract($pageData);
    
    ob_start();
    include $templatePath;
    return ob_get_clean();
}

// Now check if DataBuilder has this page and get content
if (class_exists('DataBuilder\Theme\ImscpThemeFallback')) {
    if (databuilder_has_page($currentPage, 'admin')) {
        // Get external data from iMSCP (injected by the plugin)
        global $dbVariables;
        $externalData = is_array($dbVariables ?? null) ? $dbVariables : [];
        
        // Render DataBuilder content
        $dbContent = databuilder_get_content($currentPage, 'admin', $externalData);
    }
}

?>
<!DOCTYPE html>
<html class="dark h-full" lang="en">
<head>
    <title>{TR_PAGE_TITLE}</title>
    <meta charset="{THEME_CHARSET}">
    <meta name="robots" content="nofollow, noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{THEME_ASSETS_PATH}/images/favicon.ico">
    <link rel="stylesheet" href="{THEME_ASSETS_PATH}/css/theme.css?v={THEME_ASSETS_VERSION}">
    <script src="{THEME_ASSETS_PATH}/js/jquery/jquery.js?v={THEME_ASSETS_VERSION}"></script>
    <script src="{THEME_ASSETS_PATH}/js/jquery/jquery-ui.js?v={THEME_ASSETS_VERSION}"></script>
    <script src="{THEME_ASSETS_PATH}/js/jquery/plugins/pGenerator.js?v={THEME_ASSETS_VERSION}"></script>
    <script src="{THEME_ASSETS_PATH}/js/imscp.min.js?v={THEME_ASSETS_VERSION}"></script>
    <script src="{THEME_ASSETS_PATH}/js/ui.js?v={THEME_ASSETS_VERSION}"></script>
    <script src="{THEME_ASSETS_PATH}/js/sidebar-search.js?v={THEME_ASSETS_VERSION}" defer></script>
    <?php if (($_SESSION['user_type'] ?? '') === 'admin'): ?>
    <script src="{THEME_ASSETS_PATH}/js/roles/admin.js?v={THEME_ASSETS_VERSION}" defer></script>
    <?php elseif (($_SESSION['user_type'] ?? '') === 'reseller'): ?>
    <script src="{THEME_ASSETS_PATH}/js/roles/reseller.js?v={THEME_ASSETS_VERSION}" defer></script>
    <?php else: ?>
    <script src="{THEME_ASSETS_PATH}/js/roles/client.js?v={THEME_ASSETS_VERSION}" defer></script>
    <?php endif; ?>
    <script src="{THEME_ASSETS_PATH}/js/shortcuts.js?v={THEME_ASSETS_VERSION}" defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet" />

    <script>    
        imscp_i18n = {JS_TRANSLATIONS};
    </script>
    <style>
        .topbar-actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
        .topbar-actions > * { flex-shrink: 0; }
        .topbar-icon-btn {
            width: 36px; height: 36px; min-width: 36px;
            display: inline-flex; align-items: center; justify-content: center; padding: 0;
            border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #f1f5f9; color: #475569;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        }
        .topbar-icon-btn .material-symbols-outlined {
            display: inline-flex; align-items: center; justify-content: center;
            font-family: "Material Symbols Outlined"; font-size: 20px; line-height: 1;
        }
        .topbar-icon-btn:hover { background: #e2e8f0; }
        .dark .topbar-icon-btn { border-color: #334155; background: #1f2937; color: #cbd5e1; }
        .dark .topbar-icon-btn:hover { background: #334155; }
        .topbar-search-trigger { width: 40px; height: 40px; min-width: 40px; max-width: 40px; }
        .topbar-search-trigger .topbar-search-label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .topbar-back-label { display: none; font-size: 12px; color: #4e90f4; white-space: nowrap; }
        .topbar-back-btn { gap: 6px; }
        .topbar-back-btn .material-symbols-outlined { flex: 0 0 auto; }
        @media (min-width: 1024px) {
            .topbar-back-btn { width: auto; min-width: 40px; height: 40px; padding: 0 10px; }
            .topbar-back-label { display: inline-flex; align-items: center; }
        }
        @media (max-width: 1023px) {
            .topbar-back-btn { width: 36px; min-width: 36px; padding: 0; }
            .topbar-back-btn .topbar-back-label { display: none !important; }
        }
        @media (min-width: 768px) { .topbar-search-trigger { width: min(38vw, 460px) !important; max-width: 460px !important; } }
        @media (min-width: 640px) { .topbar-actions { gap: 8px; } .topbar-icon-btn:not(.topbar-back-btn) { width: 40px; height: 40px; min-width: 40px; } }
    </style>
</head>
<body
    data-role="{THEME_ROLE}"
    data-user-id="<?= tohtml((string)($_SESSION['user_id'] ?? '0'), 'htmlAttr'); ?>"
    data-user-type="<?= tohtml((string)($_SESSION['user_type'] ?? ''), 'htmlAttr'); ?>"
    class="bg-background-light h-full dark:bg-background-dark font-display text-slate-900 dark:text-white overflow-hidden">
    
    <div id="page-loader" class="bg-background-light dark:bg-background-dark">
        <style>
            #page-loader { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.5s ease, visibility 0.5s ease; }
            #page-loader.hidden { opacity: 0; visibility: hidden; }
            .wave-container { display: flex; align-items: flex-end; justify-content: center; gap: 8px; height: 60px; }
            .wave-bar { width: 12px; height: 40px; background: linear-gradient(180deg, #3b82f6, #1d4ed8); border-radius: 100% 100% 0 0; animation: wave 1.2s ease-in-out infinite; }
            .dark .wave-bar { background: linear-gradient(180deg, #60a5fa, #3b82f6); }
            .wave-bar:nth-child(1) { animation-delay: 0s; }
            .wave-bar:nth-child(2) { animation-delay: 0.15s; }
            .wave-bar:nth-child(3) { animation-delay: 0.3s; }
            @keyframes wave { 0%, 100% { height: 40px; } 50% { height: 0px; } }
        </style>
        <div class="wave-container">
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
        </div>
        <script>window.addEventListener('load', function() { document.getElementById('page-loader').classList.add('hidden'); });</script>
    </div>

    <div class="flex min-h-screen h-full overflow-hidden">
        <!-- SIDEBAR -->
        <aside id="sidebar" class="sidebar-expanded w-60 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark flex flex-col shrink-0 fixed lg:static inset-y-0 left-0 z-50 transform -translate-x-full lg:translate-x-0 transition-all duration-300">
            <div class="p-4 shrink-0 flex items-start justify-start cursor-pointer" id="sidebar-logo">
                <img src="{ISP_LOGO}" alt="i-MSCP logo" class="w-auto h-12 sidebar-logo-full">
                <div class="sidebar-logo-collapsed hidden flex items-center justify-center">
                    <img src="{THEME_ASSETS_PATH}/images/favicon.ico" alt="logo" class="w-7 h-7">
                </div>
            </div>
            <nav class="flex-1 ml-7 overflow-y-auto modern-scrollbar flex flex-col">
                {SIDEBAR_NAVIGATION}
            </nav>
            <div class="p-4 border-t border-slate-200 dark:border-white/5 shrink-0">
                <div class="flex items-center gap-3 p-2 bg-slate-50 dark:bg-white/5 rounded-xl">
                    <div class="size-8 rounded-full bg-slate-200 dark:bg-white/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">person</span>
                    </div>
                    <div class="flex-1 min-w-0 sidebar-footer-text">
                        <?php $config = \iMSCP\Registry::get('config'); ?>
                        <p class="text-xs font-bold truncate">i-MSCP <?= tohtml($config['Version'] ?: 'Unknown')?></p>
                        <p class="text-[10px] text-slate-500 dark:text-[#9ca8ba] truncate">Build: <?= tohtml($config['Build'] ?: 'Unknown') ?></p>
                    </div>
                </div>
            </div>
        </aside>
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden"></div>

        <!-- MAIN CONTENT -->
        <main class="flex-1 flex flex-col min-w-0 bg-background-light dark:bg-background-dark overflow-hidden">
            <header class="bg-white dark:bg-background-dark border-b border-slate-200 dark:border-slate-800 flex flex-col shrink-0">
                <div class="flex items-center gap-1.5 sm:gap-2 md:gap-4 px-2 sm:px-4 md:px-8 py-2.5 md:py-4">
                    <button id="mobile-menu-toggle" class="lg:hidden p-2 rounded-lg bg-slate-100 dark:bg-[#1f2937] text-slate-600 dark:text-slate-300">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="flex items-center gap-2 min-w-0 flex-1 sm:flex-none">
                        <h1 class="text-base sm:text-xl font-bold text-slate-900 dark:text-white max-w-[34vw] sm:max-w-none overflow-hidden whitespace-nowrap text-ellipsis">
                            {PAGE_TITLE}
                        </h1>
                    </div>
                    <div class="flex-none sm:flex-1 flex justify-center min-w-0">
                        <div id="search-trigger" role="button" tabindex="0" style="display:flex;" class="topbar-search-trigger items-center justify-center md:justify-start gap-2 md:gap-3 px-0 md:px-4 py-0 md:py-2 rounded-md border cursor-pointer transition-all">
                            <span class="material-symbols-outlined text-[16px]" style="margin-left:5px;">search</span>
                            <span class="topbar-search-label hidden md:block flex-1 text-[13px]">Recherche Globale...</span>
                            <kbd id="search-kbd" class="hidden xl:inline-flex" style="margin-right:5px;"><span>Ctrl+K</span></kbd>
                        </div>
                    </div>
                    <div class="topbar-actions">
                        <div class="relative">
                            <div id="notif-btn" role="button" tabindex="0" class="topbar-icon-btn cursor-pointer" style="display:inline-flex;align-items:center;justify-content:center;" title="<?= tohtml(tr('Notifications'), 'htmlAttr') ?>">
                                <span class="material-symbols-outlined">notifications</span>
                            </div>
                        </div>
                        <div class="relative">
                            <button id="theme-toggle" class="topbar-icon-btn" title="<?= tohtml(tr('Theme'), 'htmlAttr') ?>">
                                <span class="material-symbols-outlined" id="theme-icon">light_mode</span>
                            </button>
                            <div id="theme-menu" class="hidden absolute right-0 mt-2 w-40 bg-white dark:bg-[#1f2937] border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg overflow-hidden z-50">
                                <button onclick="setTheme('light')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors">
                                    <span class="material-symbols-outlined text-lg">light_mode</span>
                                    <span class="text-slate-900 dark:text-white font-medium">Light</span>
                                </button>
                                <button onclick="setTheme('dark')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors">
                                    <span class="material-symbols-outlined text-lg">dark_mode</span>
                                    <span class="text-slate-900 dark:text-white font-medium">Dark</span>
                                </button>
                                <button onclick="setTheme('auto')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors">
                                    <span class="material-symbols-outlined text-lg">contrast</span>
                                    <span class="text-slate-900 dark:text-white font-medium">Auto</span>
                                </button>
                            </div>
                        </div>
                        <?php if(isset($_SESSION['logged_from'])):?>
                        <div class="flex items-center">
                            <a class="topbar-icon-btn topbar-back-btn" href="change_user_interface.php?action=go_back" title="<?= tohtml(tr('Back to %s', $_SESSION['logged_from']), 'htmlAttr') ?>">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                <span class="topbar-back-label"><?= tohtml(tr('Back to %s', $_SESSION['logged_from']))?></span>
                            </a>
                        </div>
                        <?php endif ?>
                        <a href="/logout" class="topbar-icon-btn" title="<?= tohtml(tr('Logout'), 'htmlAttr') ?>">
                            <span class="material-symbols-outlined">logout</span>
                        </a>
                    </div>
                </div>
                <div id="top-subnav-wrap" class="px-3 sm:px-4 md:px-8 border-t border-slate-100 dark:border-slate-800">
                    <div id="top-subnav-mobile" class="py-2"></div>
                    <div id="top-subnav-scroll" class="overflow-x-auto no-scrollbar">
                        <nav id="top-subnav" class="w-full">
                            {BREADCRUMB}
                        </nav>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <div id="content-scroll-area" class="flex-1 modern-scrollbar overflow-y-auto" style="padding: clamp(10px, 2.4vw, 32px);">
                <div class="max-w-7xl mx-auto">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
                        <span>{PAGE_HEADING}</span>
                    </h2>
                    <div class="mb-6 breadcrumb">
                        <ul class="flex items-center gap-2 text-sm text-slate-500 dark:text-[#9ca8ba]">
                            {BREADCRUMB_LIST}
                        </ul>
                    </div>
                    <div id="notice" class="{MESSAGE_CLS} mb-6 p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#1a222f] shadow-sm" style="display: {MESSAGE_DISPLAY};">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary">info</span>
                            <div class="flex-1 text-slate-700 dark:text-slate-300">{MESSAGE}</div>
                        </div>
                    </div>
                    <!-- Main content area - DataBuilder content goes here -->
                    <div class="bg-white dark:bg-[#1a222f] rounded-xl border border-slate-200 dark:border-slate-800" style="padding: clamp(12px, 2vw, 24px);">
                        <?php if (!empty($dbContent)): ?>
                        <?php echo $dbContent; ?>
                        <?php else: ?>
                        {LAYOUT_CONTENT}
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Search Modal -->
    <div id="search-modal" class="fixed inset-0 z-[100]" style="display:none;align-items:flex-start;justify-content:center;padding-top:5vh;">
        <div id="search-backdrop" style="position:absolute;inset:0;background:rgba(0,0,0,0.35);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);"></div>
        <div class="modal-box" style="position:relative;width:100%;max-width:580px;margin:0 1rem;border-radius:12px;overflow:hidden;box-shadow:0 25px 50px rgba(0,0,0,0.4);">
            <div class="modal-input-row" style="display:flex;align-items:center;gap:12px;padding:0 16px;border-bottom-width:1px;">
                <span class="material-symbols-outlined" style="font-size:18px;flex-shrink:0;">search</span>
                <input id="global-search" type="text" placeholder="Recherche Globale..." autocomplete="off" class="modal-input" style="flex:1;padding:14px 0;font-size:14px;border:none;outline:none;background:transparent;">
                <kbd style="padding:2px 6px;border-radius:4px;font-size:11px;border-width:1px;flex-shrink:0;">Esc</kbd>
            </div>
            <div id="search-results" class="modern-scrollbar" style="max-height:380px;overflow-y:auto;">
                <div id="search-empty" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 16px;">
                    <span class="material-symbols-outlined" style="font-size:36px;opacity:0.4;">manage_search</span>
                    <p style="font-size:13px;margin-top:8px;">Commencez à taper pour rechercher...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notification panel -->
    <div id="notif-panel" style="position:fixed;top:0;right:0;bottom:0;width:380px;max-width:100vw;z-index:9999;display:flex;flex-direction:column;background:#fff;border-left:1px solid #e2e8f0;box-shadow:-8px 0 40px rgba(0,0,0,.15);transform:translateX(110%);transition:transform .3s cubic-bezier(.4,0,.2,1);">
        <div id="notif-header" style="display:flex;align-items:center;gap:10px;padding:18px 20px 14px;border-bottom:1px solid #e2e8f0;flex-shrink:0;">
            <span class="material-symbols-outlined" style="font-size:20px;color:#4e90f4;">notifications</span>
            <h2 style="flex:1;font-size:.95rem;font-weight:700;color:#0f172a;margin:0;">Notifications</h2>
            <div id="notif-close" role="button" tabindex="0" style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;cursor:pointer;background:#f1f5f9;color:#64748b;transition:background .15s;">
                <span class="material-symbols-outlined" style="font-size:17px;">close</span>
            </div>
        </div>
        <div id="notif-list" class="modern-scrollbar" style="flex:1;overflow-y:auto;padding:8px 0;"></div>
    </div>

    <button id="back-to-top" type="button" aria-label="Back to top" title="Back to top" class="fixed right-3 bottom-4 sm:right-5 sm:bottom-6 z-[80] inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 text-slate-600 dark:text-slate-200 shadow-lg shadow-slate-900/15 dark:shadow-black/30 backdrop-blur transition-all duration-200 opacity-0 translate-y-2 pointer-events-none hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/40">
        <span class="material-symbols-outlined text-[20px] leading-none">arrow_upward</span>
    </button>
</body>
</html>

