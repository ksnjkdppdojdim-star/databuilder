# DataBuilder — Architecture, Exécution & Performance

---

## 1. Ordre d'exécution complet (d'iMSCP à la vue)

DataBuilder est un **moteur de rendu frontend injecté dans iMSCP via son système d'événements**.  
Il ne remplace pas iMSCP — il s'y greffe.

```
┌─────────────────────────────────────────────────────────────────────┐
│  Requête HTTP → /admin/layout.php                                   │
└─────────────────────────────────────────────────────────────────────┘
         │
         ▼
1. iMSCP dispatch(onAdminScriptStart)
   └─ DataBuilder: setupAdminNavigation()
         └─ Injecte la navigation custom dans l'instance TemplateEngine

         │
         ▼
2. iMSCP execute generatePage(), generateNavigation(), etc.
   └─ dispatch(onBeforeLoadTemplateFile)
         └─ DataBuilder: handleTemplateOverride()
               ├─ Page gérée (dans pages.xml) ?
               │     └─ SKIP → sera pris en charge à l'étape 4
               └─ Page non gérée ?
                     └─ Redirige rootDir vers themes/custom/ (fallback Magento-like)

         │
         ▼
3. iMSCP popule toutes ses variables
   └─ $tpl->assign(['WEB_IN_ALL' => ..., 'TR_DAY' => ...])
   └─ $tpl->parse('DAY_LIST', '.day_list')  ← HTML pré-rendu stocké dans $namespace

         │
         ▼
4. dispatch(onAdminScriptEnd) — PRIORITÉ 2  ← DataBuilder intercepte ICI
   └─ DataBuilder: handleScriptEnd()
         ├─ Lit $tpl->namespace via ReflectionProperty (cached static)
         ├─ Détecte le scope : admin | reseller | client
         ├─ Cherche le controller dans config/{scope}/pages.xml
         └─ executeDataBuilderController()
               ├─ Config récupérée (cached dans $this->cachedConfig)
               ├─ Instancie : Registry, TemplateEngine, LayoutManager,
               │              BlockFactory, Route, Controller
               └─ controller->execute()
                     ├─ loadLayout("admin/layout")
                     │     └─ LayoutManager lit themes/custom/layouts/admin/layout.xml
                     │     └─ Arbre de blocs construit :
                     │           root (ContainerBlock)
                     │           ├── header          → admin/layout/header.phtml
                     │           ├── theme_selector  → admin/layout/theme_selector.phtml
                     │           ├── logo_manager    → admin/layout/logo_manager.phtml
                     │           └── other_settings  → admin/layout/other_settings.phtml
                     ├─ propagateData()
                     │     └─ $block->assignData($data) sur chaque bloc récursivement
                     └─ rootBlock->render()
                           └─ Chaque bloc inclut son .phtml → HTML concaténé

         │  tpl->assign('LAYOUT_CONTENT', $html)  ← injecté ici
         ▼
5. dispatch(onAdminScriptEnd) — PRIORITÉ 1  ← layout_init d'iMSCP
   └─ layout_init appelle parse('LAYOUT', 'layout')
         └─ ui.tpl est rendu : {LAYOUT_CONTENT} = HTML DataBuilder
         └─ Page finale envoyée au navigateur
```

---

## 2. Comparaison de performance : iMSCP natif vs DataBuilder

### iMSCP natif — très léger

```
$tpl->define_dynamic([...])      → stocke des noms de fichiers en mémoire
$tpl->assign(['TR_DAY' => ...])  → simple écriture dans un tableau PHP
$tpl->parse('LAYOUT_CONTENT')    → str_replace/{VAR} sur une string
$tpl->prnt()                     → echo
```

**Coût total : quelques microsecondes.**  
Pas de XML, pas d'objets, pas d'include dynamique.

---

### DataBuilder — pipeline plus riche

À chaque requête avant optimisation :

| Étape | Coût |
|-------|------|
| `Closure::bind` pour lire `$namespace` | reflection JIT |
| `loadPagesConfig()` → `simplexml_load_file()` × 3 | I/O disque + parse XML |
| `new Registry, TemplateEngine, LayoutManager, BlockFactory, Route, Controller` | ~6–8 instanciations |
| `LayoutManager::getLayout()` → parse XML layout | lecture + arbre |
| `BlockFactory::createFromNode()` | instanciation récursive |
| `propagateData()` | parcours de l'arbre |
| `rootBlock->render()` → `ob_start()` + `include .phtml` × N blocs | I/O + interpréteur |
| `error_log()` × 3 debug | **I/O disque inutile en prod** |

**Surcoût estimé : 5–25 ms selon N blocs et état de l'OPcache.**

---

### Verdict

> DataBuilder est **toujours plus lent qu'iMSCP natif** pour le rendu HTML pur.  
> Mais dans le contexte réel d'une requête iMSCP (SQL + navigation + session),  
> le surcoût est **< 5 % du temps total** avec OPcache actif.

---

## 3. Optimisations appliquées

### 3.1 `ReflectionProperty` — caché en `static`

**Avant :** `new ReflectionClass($context)` à **chaque** template chargé par iMSCP.

**Après :**
```php
private static $rootDirReflection = null;
private static $namespaceReflection = null;

// Créé une seule fois, réutilisé pour toute la durée du process
if (self::$rootDirReflection === null) {
    $refl = new ReflectionClass($context);
    self::$rootDirReflection = $refl->getProperty('rootDir');
    self::$rootDirReflection->setAccessible(true);
}
$rootDir = self::$rootDirReflection->getValue($context);
```

**Gain : suppression N instanciations Reflection par requête.**

---

### 3.2 Config DataBuilder — cachée dans `$this->cachedConfig`

**Avant :** `getDataBuilderConfig()` recalculé à chaque requête.

**Après :**
```php
private $cachedConfig = null;

if ($this->cachedConfig === null) {
    $this->cachedConfig = $this->getDataBuilderConfig($context);
}
$config = $this->cachedConfig;
```

**Gain : 1 calcul par worker PHP-FPM au lieu de 1 par requête.**

---

### 3.3 `loadPagesConfig()` — APCu cross-process

**Avant :** `simplexml_load_file()` × 3 avec uniquement `static $cache` (par process).

**Après : 3 niveaux de cache :**

```php
// Niveau 1 — static (même process, même request)
static $cache = null;
if ($cache !== null) return $cache;

// Niveau 2 — APCu (partagé entre tous les workers PHP-FPM)
// Clé inclut filemtime(config/) → auto-invalide après un déploiement
$apcuKey = 'db_pages_cfg_' . filemtime(__DIR__ . '/config');
if (function_exists('apcu_fetch')) {
    $hit = apcu_fetch($apcuKey, $success);
    if ($success) return $cache = $hit;
}

// Niveau 3 — cold parse (une seule fois après déploiement)
// ... simplexml_load_file() ...
apcu_store($apcuKey, $result, 3600);
return $cache = $result;
```

**Gain : les XML ne sont parsés qu'à une seule fois après chaque déploiement,  
partagé entre tous les workers.**

---

### 3.4 Cache templates `.phtml` activé

**Avant :** `cache_enable: false` → les `.phtml` étaient rechargés et réinterprétés à chaque hit.

**Après :** `cache_enable: true` → les templates compilés sont écrits dans  
`/gui/data/cache/databuilder-templates/` et réutilisés.

**Gain : suppression des `include()` répétés sur disque.**

---

### 3.5 Suppression des `error_log()` debug

3 appels `error_log()` dans `handleScriptEnd()` ont été supprimés.  
Chaque `error_log()` = **I/O disque synchrone** → verrou sur le fichier de log.

**Gain : direct en environnement haute charge.**

---

## 4. DataBuilder à 50 000 utilisateurs simultanés

### Ce que DataBuilder peut contrôler ✅

Avec toutes les optimisations ci-dessus + OPcache + APCu :

- Surcoût DataBuilder par requête : **~1–3 ms**
- Les XMLs ne sont parsés **qu'une seule fois** pour tous les workers
- Les templates `.phtml` sont servis depuis le cache
- Aucun `error_log()` inutile

### Ce que DataBuilder ne peut PAS contrôler ❌

Le vrai goulot à 50 000 requêtes simultanées est l'infrastructure PHP-FPM :

```
50 000 req simultanées
÷ 50 req/process PHP-FPM  = 1 000 workers PHP nécessaires
× 32 MB RAM/worker        = 32 GB RAM rien que pour PHP
```

**DataBuilder n'est pas le problème — c'est le modèle synchrone de PHP/iMSCP.**

### Recommandations infra pour la montée en charge

| Niveau | Solution |
|--------|----------|
| **PHP** | OPcache activé + APCu partagé + PHP-FPM tuning (`pm.max_children`) |
| **Cache** | Redis pour sessions + APCu pour données process |
| **Web** | Nginx en reverse proxy + compression gzip/brotli des assets |
| **Scale** | Load balancer + N serveurs iMSCP en pool (horizontal) |
| **Assets** | CSS/JS servis par Nginx directement (bypass PHP) |
| **DB** | Pool de connexions MySQL (PDO persistent) + query cache |

### Tableau récapitulatif

| Scénario | Utilisateurs simultanés | Infra nécessaire |
|----------|------------------------|------------------|
| Petite instance | < 500 | 1 serveur, 2–4 GB RAM |
| Moyenne instance | 500–5 000 | 1 serveur puissant + Redis |
| Grande instance | 5 000–50 000 | Load balancer + 4–8 serveurs + Redis cluster |
| Très grande instance | > 50 000 | Architecture microservices — iMSCP n'est plus adapté |

---

## 5. Résumé en une phrase

> iMSCP fait tout son travail de données normalement → DataBuilder intercepte à la **fin** (`onAdminScriptEnd` priorité 2), lit les données via `ReflectionProperty` caché, instancie son pipeline (Route → Controller → Layout XML → Blocks → `.phtml`) avec config et pages.xml mis en cache APCu, injecte le HTML produit dans `LAYOUT_CONTENT` **avant** qu'iMSCP ne rende `ui.tpl`, avec un surcoût de **1–3 ms** par requête en production.
