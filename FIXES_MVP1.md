# 🔧 DataBuilder MVP1 - Corrections d'Architecture

## ❌ Problèmes identifiés

1. **CSS/JS pas chargés**: DataBuilder retournait HTML brut, sans passer par iMSCP
2. **Variables ne s'affichent pas**: `{PAGE_TITLE}`, `{SIDEBAR}` non remplacées - car iMSCP ne voyait jamais le contenu
3. **CleanupManager non trouvé**: Autoloader PSR-4 incorrect
4. **Architecture mauvaise**: DataBuilder rendait page complète au lieu de contenu seulement

## ✅ Solutions implémentées

### 1. **Nouvelle Architecture: Event Hooks iMSCP**

**ANCIEN FLOW** (brisé):
```
User → /admin/index.php
   ↓
DataBuilder filme page & returns HTML
   ↓
Envoie au browser (sans wrapper iMSCP, sans variables remplacées)
```

**NOUVEAU FLOW** (fonctionnel):
```
User → /admin/index.php
   ↓
iMSCP charge template normal: ui.tpl
   ↓
iMSCP Fire event: onAfterBuildTemplate
   ↓
DataBuilder hook: injectDataBuilderContent()
   → Renders page content via renderPageContent()
   → Injecte dans {LAYOUT_CONTENT}
   ↓
iMSCP Fire event: onAfterLoadTemplateFile
   ↓
DataBuilder hook: injectDataBuilderAssets()
   → Ajoute CSS/JS via script inline
   ↓
iMSCP remplace les variables {PAGE_TITLE}, {ADMIN_USERS}, etc.
   ↓
Envoie au browser (avec wrapper + design + variables remplacées)
```

### 2. **Gestionnaire d'Assets Sécurisé**

Créé: `asset-serve.php`
- Sert CSS/JS en toute sécurité
- Whitelisting des fichiers
- Prévention directory traversal
- GZIP compression
- CORS support

### 3. **ImscpBridge pour Accès aux Variables**

Créé: `src/Integration/ImscpBridge.php`
- Accès sécurisé aux variables iMSCP
- Pas de DB queries
- Cache des variables
- Accessible depuis les blocks

### 4. **Autoloader PSR-4 Fixé**

Amélioré dans: `DataBuilderIMSCPBetaPlugin.php`
- Chargement correct des classes DataBuilder\*
- Support pour DataBuilder\Cleanup\CleanupManager
- Fallback Composer autoload
- Meilleure gestion d'erreurs

---

## 📝 Modification Manuelle Requise

Le fichier `src/Block/Admin/IndexBlock.php` doit être remplacé.

**Copier le contenu du fichier:**
```
src/Block/Admin/IndexBlock-new.php  →  src/Block/Admin/IndexBlock.php
```

Ou manuellement:
```bash
cd c:\Users\Jules\Documents\iMSCP\databuilder
# sur Mac/Linux:
rm src/Block/Admin/IndexBlock.php
mv src/Block/Admin/IndexBlock-new.php src/Block/Admin/IndexBlock.php

# sur Windows (PowerShell):
rm src\Block\Admin\IndexBlock.php
mv src\Block\Admin\IndexBlock-new.php src\Block\Admin\IndexBlock.php
```

---

## 🔄 Architecture Finale

```
DataBuilderIMSCPBetaPlugin/
├── DataBuilderIMSCPBetaPlugin.php
│   ├── initAutoloader()           ✅ Fixé - charge toutes les classes DataBuilder
│   ├── register()                 
│   │   ├── injectDataBuilderContent() ✅ NOUVEAU - remplace {LAYOUT_CONTENT}
│   │   ├── injectDataBuilderAssets()  ✅ NOUVEAU - injecte CSS/JS
│   │   └── autres hooks...
│   └── uninstall()                ✅ Utilise CleanupManager
│
├── asset-serve.php                ✅ NOUVEAU - sert les assets en sécurité
│
└── src/
    ├── Core/Engine.php            ✅ renderPageContent() - retourne contenu seul
    ├── Block/Admin/IndexBlock.php  ✅ NOUVEAU - utilise ImscpBridge
    ├── Integration/ImscpBridge.php ✅ NOUVEAU - accès variables iMSCP
    ├── Cleanup/CleanupManager.php  ✅ Maintenant trouvable
    └── Asset/AssetManager.php      (peut être utilisé plus tard)
```

---

## 🧪 Nouveau Processus de Test

### 1. **Pré-installation**
```bash
# Vérifier que IndexBlock.php a été remplacé
ls -la c:\Users\Jules\Documents\iMSCP\databuilder\src\Block\Admin\IndexBlock.php
# Doit exister et contenir ImscpBridge
```

### 2. **Créer le ZIP**
```powershell
cd c:\Users\Jules\Documents\iMSCP\databuilder
Compress-Archive -Path ./* -DestinationPath ../DataBuilderMVP1-v3.zip -Force
```

### 3. **Installer dans iMSCP**
- Admin → Plugins
- Upload ZIP
- Install & Enable

### 4. **Tester**
Aller à: `http://your-imscp/admin/index`

Vérifier:
1. ✅ Les **variables** iMSCP remplacées (chiffres visibles)
2. ✅ Le **design** s'affiche (cartes en grille, pas horizontal)
3. ✅ Les **CSS/JS** chargés (F12 → Elements → chercher `databuilder.css`)
4. ✅ **Console F12** sans erreurs

### 5. **Debugging**
```javascript
// F12 → Console:
window.DataBuilderDebug.getStats()  // Voir les stats
window.DataBuilderDebug.getColors() // Voir les couleurs CSS
```

---

## 🐛 Résolution des Problèmes Restants

### Si variables toujours s'affichent textuellement:
1. Vérifier que le hook `onAfterBuildTemplate` s'exécute
2. Vérifier que `injectDataBuilderContent()` remplace bien `{LAYOUT_CONTENT}`
3. Checker les logs: `/imscp/gui/logs/` pour erreurs

### Si CSS/JS toujours pas chargés:
1. Vérifier que le hook `onAfterLoadTemplateFile` s'exécute
2. Vérifier que `asset-serve.php` est accessible
3. F12 → Network → voir si `.css` et `.js` ont statut 200 ou 404

### Si `ClassLoader error` lors de désinstallation:
- Erreur résolue avec nouvel autoloader
- Si persiste: vérifier que `src/Cleanup/CleanupManager.php` existe

---

## 📊 Comparaison Avant/Après

| Aspect | Avant | Après |
|--------|-------|-------|
| CSS Chargé | ❌ Non | ✅ Oui (asset-serve.php) |
| Variables remplacées | ❌ Non | ✅ Oui (hook iMSCP) |
| Contenu injecté | ❌ Page blanche | ✅ Dans {LAYOUT_CONTENT} |
| Design | ❌ Rien | ✅ Grille responsive |
| CleanupManager chargé | ❌ Class not found | ✅ Oui (autoloader fixé) |
| Integration iMSCP | ❌ Aucune | ✅ Hooks événements |

---

## 🎯 Prochaines Étapes

1. ✅ **Remplacer IndexBlock.php** (URGENT)
2. ✅ **Créer ZIP et tester** 
3. 🔄 **Rapporter les résultats** (variables affichées? CSS OK? Erreurs?)
4. 📋 **Créer MVP2** après confirmation MVP1 fonctionne

---

## 💾 Fichiers Modifiés/Créés

```
✅ MODIFIÉ:
- DataBuilderIMSCPBetaPlugin.php (hooks events)
- src/Core/Engine.php (renderPageContent)

✅ CRÉÉ:
- asset-serve.php (gestionnaire assets)
- src/Integration/ImscpBridge.php (accès variables iMSCP)
- src/Block/Admin/IndexBlock-new.php (utilise ImscpBridge)
- src/Cleanup/CleanupManager.php (cleanup complet)

ℹ️ À REMPLACER:
- src/Block/Admin/IndexBlock.php ← À copier depuis IndexBlock-new.php
```

---

**Dépêche-toi de tester et dis-moi si ça marche! 🎯**
