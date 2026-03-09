# DataBuilder iMSCP Plugin - Implementation TODO

## Architecture Clarification

**IMPORTANT:**
- DataBuilder utilise: `layout.xml`, `block.php`, et `template.phtml`
- **PAS de fichiers .tpl** - les .tpl sont pour les thèmes basiques iMSCP
- DataBuilder doit s'intégrer à iMSCP en tant que **moteur de rendu** qui s'adapte
- DataBuilder **NE fait JAMAIS de requêtes base de données** - il compose et rend les vues

---

## Phase 1: Theme Integration (DataBuilder comme thème iMSCP)

### 1.1 Structure du thème DataBuilder
**Objectif:** Créer databuilder comme un thème iMSCP dans `gui/themes/databuilder/`

```
var/www/imscp/gui/themes/databuilder/
├── info.php                    # Info du thème (requis par iMSCP)
├── index.tpl                   # Index minimal (Délègue à DataBuilder)
├── admin/
│   ├── databuilder.phtml      # Page admin DataBuilder
│   └── ...
├── client/
│   └── ...
├── shared/
│   └── layouts/
│       └── ui.tpl              # Layout de base iMSCP
└── assets/
    └── (CSS/JS si besoin)
```

### 1.2 Info.php du thème
**Créer:** `themes/databuilder/info.php`
```php
<?php
return [
    'theme_name'    => 'DataBuilder',
    'theme_version' => '1.0.0',
    'theme_author'  => 'Jules MAHOUNOU',
    'theme_desc'    => 'Magento-like modular theming for i-MSCP',
    'theme_type'    => 'frontend' // ou 'backend'
];
```

### 1.3 Index.tpl minimal
**Créer:** `themes/databuilder/index.tpl`
```html
<!-- Minimal index - redirects to DataBuilder engine -->
<!-- This is required by iMSCP but DataBuilder handles rendering -->
<html>
<head><title>DataBuilder</title></head>
<body>
    <!-- DataBuilder will inject content here -->
    <!-- Use iMSCP layout system -->
</body>
</html>
```

---

## Phase 2: Template Engine Integration

### 2.1 Moteur de rendu hybride
**Concept:** DataBuilder doit détecter s'il doit gérer une page ou déléguer à iMSCP

**Approche:** 
- DataBuilder fournit ses propres pages via `layout.xml` + `block.php` + `template.phtml`
- Pour les pages iMSCP non implémentées dans DataBuilder → Déléguer au thème iMSCP

### 2.2 Configuration des layouts DataBuilder
**Fichier:** `themes/databuilder/layouts/admin.xml`
```xml
<?xml version="1.0"?>
<layout>
    <default>
        <block name="header" class="DataBuilder\Block\ImscpHeader"/>
        <block name="content" class="DataBuilder\Block\ContentBlock"/>
        <block name="footer" class="DataBuilder\Block\ImscpFooter"/>
    </default>
    
    <admin_databuilder>
        <block name="page.content" template="admin/databuilder.phtml"/>
    </admin_databuilder>
</layout>
```

### 2.3 Template.phtml pour DataBuilder
**Créer:** `themes/databuilder/templates/admin/databuilder.phtml`
```php
<?php
/** @var DataBuilder\Core\Engine $engine */
$engine = $this->engine;
?>
<div class="databuilder-admin">
    <!-- DataBuilder renders blocks here -->
    <!-- Uses $this->block('name')->render() -->
</div>
```

---

## Phase 3: Route & Controller

### 3.1 Système de routes DataBuilder
**Fichier:** `config/routes.xml`
```xml
<?xml version="1.0"?>
<routes>
    <!-- Admin Routes -->
    <route path="/admin/databuilder">
        <controller>DataBuilder\Controller\Admin\DataBuilderController</controller>
        <action>index</action>
    </route>
    
    <!-- Page dynamique: Vérifie si DataBuilder a la page -->
    <route path="/admin/{page}">
        <controller>DataBuilder\Controller\ImscpPageController</controller>
        <action>dispatch</action>
    </route>
    
    <!-- Client Routes -->
    <route path="/client/{page}">
        <controller>DataBuilder\Controller\ImscpPageController</controller>
        <action>dispatch</action>
    </route>
</routes>
```

### 3.2 ImscpPageController
**Nouveau fichier:** `src/Controller/ImscpPageController.php`
```php
<?php

namespace DataBuilder\Controller;

class ImscpPageController
{
    /**
     * Dispatch request - check if DataBuilder has the page
     * 
     * @param string $page Page name (e.g., 'admin_log')
     * @param string $area Area ('admin', 'client', 'reseller')
     * @return void
     */
    public function dispatch(string $page, string $area = 'admin'): void
    {
        // 1. Check if DataBuilder has layout: themes/{$area}/{$page}.xml
        // 2. Check if DataBuilder has template: themes/{$area}/{$page}.phtml
        // 3. If YES → Render with DataBuilder engine
        // 4. If NO → Let iMSCP handle it (fallback)
        
        $templatePath = $this->findTemplate($page, $area);
        
        if ($templatePath !== null) {
            // Render with DataBuilder
            $this->renderDataBuilderPage($templatePath);
        } else {
            // Delegate to iMSCP - DO NOT interfere
            $this->delegateToImscp($page, $area);
        }
    }
    
    /**
     * Find template in DataBuilder theme
     */
    private function findTemplate(string $page, string $area): ?string
    {
        $paths = [
            $this->config['themes_path'] . '/custom/' . $area . '/' . $page . '.phtml',
            $this->config['themes_path'] . '/default/' . $area . '/' . $page . '.phtml',
            $this->config['themes_path'] . '/base/' . $area . '/' . $page . '.phtml',
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Delegate to iMSCP - let it handle the page normally
     */
    private function delegateToImscp(string $page, string $area): void
    {
        // Don't render anything - let iMSCP use its default/i-mscp theme
        // This allows seamless fallback
    }
}
```

---

## Phase 4: Fix Dark Mode Error

### 4.1 Analyse de l'erreur
**Problème:** "Une erreur inattendue s'est produite" avec dark mode sur /admin/databuilder

**Causes possibles:**
1. Template non trouvé
2. Assets CSS/JS dark mode manquants
3. Moteur de template iMSCP pas correctement initialisé

### 4.2 Solution
**Modifier:** `frontend/admin/databuilder.php`

```php
<?php
/**
 * DataBuilderIMSCPPlugin - Admin Frontend Entry Point
 * 
 * Entry point for /admin/databuilder route
 * Works within i-MSCP context
 */

// Get plugin directory
$pluginDir = dirname(__DIR__, 2);

// Include DataBuilder engine
$engine = require_once $pluginDir . '/src/Core/Engine.php';

// Initialize engine with iMSCP config
$engine->init([
    'base_path'   => $pluginDir . '/src',
    'themes_path' => $pluginDir . '/themes',
    'cache_path'  => $pluginDir . '/cache',
    'debug'       => true, // Enable for debugging
]);

// Check if template exists
$templateFile = $pluginDir . '/themes/default/view/admin/databuilder.phtml';

if (!file_exists($templateFile)) {
    // Log error
    error_log('DataBuilder: Template not found - ' . $templateFile);
    
    // Show user-friendly message
    echo '<div class="alert alert-warning">';
    echo '<h4>DataBuilder</h4>';
    echo '<p>Template en cours de développement.</p>';
    echo '</div>';
    return;
}

// Render with DataBuilder
try {
    $engine->getLayoutManager()->loadLayout('admin/databuilder');
    $engine->getTemplateEngine()->render($templateFile);
} catch (\Exception $e) {
    // Log error
    error_log('DataBuilder Error: ' . $e->getMessage());
    
    // Show error
    echo '<div class="alert alert-danger">';
    echo '<h4>Erreur DataBuilder</h4>';
    echo '<p>Une erreur inattendue s\'est produite.</p>';
    if (isset($config['debug']) && $config['debug']) {
        echo '<pre>' . $e->getMessage() . '</pre>';
    }
    echo '</div>';
}
```

---

## Phase 5: Installation/Update Hooks

### 5.1 Installation - Copier les fichiers du thème
**Modifier:** `DataBuilderIMSCPBetaPlugin::install()`

```php
public function install(iMSCP_Plugin_Manager $pluginManager): void
{
    $imscpRootDir = $this->getImscpRootDir(); // Get from registry
    $themesDir = $imscpRootDir . '/gui/themes';
    
    // 1. Create databuilder theme directory
    $databuilderThemeDir = $themesDir . '/databuilder';
    if (!is_dir($databuilderThemeDir)) {
        mkdir($databuilderThemeDir, 0755, true);
    }
    
    // 2. Copy theme files from plugin
    $this->copyThemeFiles($pluginDir . '/themes', $databuilderThemeDir);
    
    // 3. Create necessary directories
    $this->createDirectories();
    
    // 4. Install dependencies
    $this->installDependencies();
    
    write_log('DataBuilderIMSCPPlugin installed successfully', E_USER_NOTICE);
}
```

### 5.2 Update - Préserver les personnalisations
```php
public function update(iMSCP_Plugin_Manager $pluginManager, string $fromVersion, string $toVersion): void
{
    // 1. Backup custom theme
    $customThemeBackup = $this->backupCustomTheme();
    
    // 2. Update base theme files
    $this->updateBaseThemeFiles();
    
    // 3. Restore custom theme (themes/custom is preserved)
    
    write_log('DataBuilderIMSCPPlugin updated to ' . $toVersion, E_USER_NOTICE);
}
```

### 5.3 Uninstall - Supprimer le thème
```php
public function uninstall(iMSCP_Plugin_Manager $pluginManager): void
{
    $imscpRootDir = $this->getImscpRootDir();
    $databuilderThemeDir = $imscpRootDir . '/gui/themes/databuilder';
    
    // Remove theme directory
    if (is_dir($databuilderThemeDir)) {
        $this->removeDir($databuilderThemeDir);
    }
    
    write_log('DataBuilderIMSCPPlugin uninstalled', E_USER_NOTICE);
}
```

---

## Phase 6: Fallback System

### 6.1 Logique de fallback
**Concept:** Si DataBuilder n'a pas une page → Déléguer à iMSCP

```
Requête /admin/admin_log
    ↓
DataBuilder vérifie: themes/databuilder/admin/admin_log.phtml?
    ├── OUI → Rendre avec DataBuilder
    └── NON → Déléguer à iMSCP (laisser iMSCP utiliser default/i-mscp)
```

### 6.2 Implémentation du fallback
**Nouveau fichier:** `src/Theme/ImscpThemeFallback.php`

```php
<?php

namespace DataBuilder\Theme;

class ImscpThemeFallback
{
    private array $config;
    private string $imscpRoot;
    
    public function __construct(array $config, string $imscpRoot)
    {
        $this->config = $config;
        $this->imscpRoot = $imscpRoot;
    }
    
    /**
     * Resolve template path with fallback to iMSCP themes
     * 
     * @param string $template Template name (e.g., 'admin/admin_log')
     * @return string|null Template path or null to delegate to iMSCP
     */
    public function resolve(string $template): ?string
    {
        // Priority order (like Magento):
        // 1. Custom theme (highest)
        // 2. Default theme
        // 3. Base theme
        // 4. Delegate to iMSCP
        
        $searchPaths = [
            $this->config['themes_path'] . '/custom/' . $template . '.phtml',
            $this->config['themes_path'] . '/default/' . $template . '.phtml',
            $this->config['themes_path'] . '/base/' . $template . '.phtml',
        ];
        
        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Not found in DataBuilder → Delegate to iMSCP
        return null;
    }
    
    /**
     * Get iMSCP theme path for fallback
     */
    public function getImscpThemePath(string $template): string
    {
        $imscpTheme = $this->getCurrentImscpTheme();
        
        return $this->imscpRoot . '/gui/themes/' . $imscpTheme . '/' . $template . '.tpl';
    }
    
    /**
     * Get current iMSCP theme
     */
    private function getCurrentImscpTheme(): string
    {
        if (defined('IMSCP_THEME')) {
            return IMSCP_THEME;
        }
        
        return 'default';
    }
}
```

---

## Phase 7: Developer Experience

### 7.1 Structure des thèmes pour développeur
```
themes/
├── base/                      # Thème de base (ne pas modifier)
│   ├── layouts/
│   │   └── admin.xml
│   ├── templates/
│   │   ├── admin/
│   │   │   └── databuilder.phtml
│   │   └── client/
│   └── theme.xml
│
├── default/                   # Thème par défaut (hérite de base)
│   ├── layouts/               # Héritage: si absent, utilise base/
│   └── templates/
│       └── admin/
│
└── custom/                    # Personnalisations développeur
    ├── layouts/
    │   └── admin.xml          # Override: remplace base/admin.xml
    └── templates/
        └── admin/
            └── databuilder.phtml  # Override: remplace base/admin/databuilder.phtml
```

### 7.2 Créer une nouvelle page (pour développeur)
```php
// 1. Créer le template: themes/custom/templates/admin/mypage.phtml
<div class="mypage">
    <h1><?= $this->translate('My Page') ?></h1>
    <!-- Content -->
</div>

// 2. Créer le layout: themes/custom/layouts/admin/mypage.xml
<?xml version="1.0"?>
<layout>
    <admin_mypage>
        <block name="page.content" template="admin/mypage.phtml"/>
    </admin_mypage>
</layout>

// 3. La page est automatiquement disponible via /admin/mypage
```

---

## Phase 8: Testing

### 8.1 Tests à effectuer

| Test | Résultat attendu |
|------|-----------------|
| Accès /admin/databuilder | Affiche page admin DataBuilder |
| Accès /admin/admin_log (non implémenté) | iMSCP affiche avec son thème |
| Mode dark | Fonctionne sans erreur |
| Installation plugin | Crée gui/themes/databuilder/ |
| Override template | Template custom utilisé |
| Créer nouvelle page | Page render avec DataBuilder |

### 8.2 Tests d'erreur

- [ ] Template manquant → Déléguer à iMSCP
- [ ] Erreur PHP → Message user-friendly
- [ ] Debug mode → Détails de l'erreur

---

## Files à Modifier/Créer

### Modifier
- [ ] `Plugins/DataBuilderIMSCPBetaPlugin/DataBuilderIMSCPBetaPlugin.php` - Logique install/uninstall
- [ ] `Plugins/DataBuilderIMSCPBetaPlugin/frontend/admin/databuilder.php` - Fix dark mode error
- [ ] `config/routes.xml` - Routes DataBuilder

### Créer
- [ ] `themes/databuilder/info.php` - Info thème iMSCP
- [ ] `themes/databuilder/index.tpl` - Index minimal
- [ ] `themes/databuilder/admin/databuilder.phtml` - Template admin
- [ ] `src/Controller/ImscpPageController.php` - Routing dynamique
- [ ] `src/Theme/ImscpThemeFallback.php` - Système fallback
- [ ] `src/Debug/Debugger.php` - Outils debug

---

## Priorité d'implémentation

### P0 - Critique
1. [ ] Fix dark mode error sur /admin/databuilder
2. [ ] Créer structure thème basic
3. [ ] Implémenter fallback vers iMSCP

### P1 - Important
4. [ ] Installer/désinstaller hooks
5. [ ] Créer template admin DataBuilder
6. [ ] Ajouter debug logging

### P2 - Moyen
7. [ ] Plus de pages templates
8. [ ] Système de modules
9. [ ] Documentation développeur

### P3 - Faible
10. [ ] Optimisation performance
11. [ ] Système de cache
12. [ ] Fonctionnalités avancées

---

## Notes Importantes

- **JAMAIS toucher:** `C:\Users\jules\Documents\iMSCP\imscp\`
- **DataBuilder:** Compose, agencement, formattage et rendu des vues - **PAS de requêtes DB**
- **Intégration:** Doit fonctionner seamlessly avec les thèmes existants iMSCP
- **Expérience développeur:** Facile à overrider sans toucher le core

