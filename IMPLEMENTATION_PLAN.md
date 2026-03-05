# 📋 DataBuilder iMSCP Plugin - PLAN D'IMPLÉMENTATION COMPLET

**Date:** Mars 5, 2026  
**Objectif:** Créer un plugin DataBuilder ultra-performant pour iMSCP qui fonctionne comme un thème modulaire  

---

## 🎯 OBJECTIF GLOBAL

Transformer le plugin DataBuilderIMSCPBetaPlugin en un moteur de rendu frontend **autonome et modulaire** qui:
- ✅ S'intègre à iMSCP comme thème sans modifier son code
- ✅ Fournit des views paramétrables via Layout/Block/Template
- ✅ Sépare les données des templates (XML/JSON)
- ✅ Permet aux développeurs de surcharger sans toucher au code source
- ✅ Fonctionne UNIQUEMENT en rendering (aucune requête DB)
- ✅ Est installable sur n'importe quel serveur iMSCP

---

## ⚡ PHASE 0: DIAGNOSTIQUE & RÉPARATION CRITIQUE

### 0.1 Fixer l'erreur "dark_mode - An unexpected error occurred"
**Ticket:** Dans `/admin/databuilder` s'affiche une erreur au chargement  
**Cause probable:** Erreur PHP dans le template ou missing variable

**Tâches:**
- [ ] **0.1.1** Vérifier les logs PHP (apache error.log ou syslog)
- [ ] **0.1.2** Tester le fichier `frontend/admin/databuilder.php` pour:
  - [ ] Syntax errors en PHP
  - [ ] Missing includes/requires
  - [ ] Undefined variables
- [ ] **0.1.3** Ajouter du debug logging dans PageRenderer.php
- [ ] **0.1.4** Verifier que les fichiers `themes/index.tpl` et `themes/default/data/admin/databuilderData.xml` existent
- [ ] **0.1.5** Tester manuellement en mode debug (`?debug=1`)
- [ ] **0.1.6** Valider XML syntax dans les fichiers de configuration

**Résultat attendu:** `/admin/databuilder` affiche une page sans style (HTML brut est acceptable)

---

## 📐 PHASE 1: ARCHITECTURE CORE STRUCTURE

### 1.1 Organiser la structure du plugin
**Objectif:** Créer une structure claire et maintenable

**Fichiers à réviser/créer:**

```
DataBuilderIMSCPBetaPlugin/
├── config/
│   ├── databuilder.xml          ✅ EXISTENT
│   ├── modules.xml              ✅ EXISTENT
│   └── routes.xml               ✅ EXISTENT
│
├── src/
│   ├── Block/                   ✅ EXISTENT
│   ├── Cache/                   ✅ EXISTENT
│   ├── Controller/              ✅ EXISTENT (ImscpPageController.php)
│   ├── Core/                    ✅ EXISTENT (Engine, etc)
│   ├── Event/                   ✅ EXISTENT
│   ├── Layout/                  ✅ EXISTENT
│   ├── Module/                  ✅ EXISTENT
│   ├── Router/                  ✅ EXISTENT
│   ├── Template/                ✅ EXISTENT
│   ├── Tenant/                  ✅ EXISTENT
│   ├── Theme/                   ✅ EXISTENT
│   ├── PageRenderer.php         ✅ EXISTENT
│   └── Debug/
│       └── Debugger.php         ⬜ À CRÉER
│
├── themes/                      ⬜ Partiellement complet
│   ├── index.tpl               ✅ EXISTENT (mais à vérifier)
│   ├── theme.xml               ✅ EXISTENT
│   ├── base/                   ✅ Partiellement
│   ├── custom/                 ✅ Vide (pour users)
│   └── default/                ✅ Partiellement
│       ├── data/               ✅ Pour XML
│       ├── layouts/            ⬜ À CRÉER
│       └── templates/          ✅ Partiellement
│
├── frontend/
│   └── admin/
│       └── databuilder.php      ⚠️ En cours de correction
│
├── integration/                 ✅ EXISTENT
├── modules/                     ✅ EXISTENT
├── data/                        ✅ EXISTENT (vide)
├── cache/                       ✅ EXISTENT
│
├── DataBuilderIMSCPBetaPlugin.php  ✅ EXISTENT (plugin principal)
├── README.md                    ✅ EXISTENT
├── config.php                   ✅ EXISTENT (configuration)
├── info.php                     ✅ EXISTENT (plugin metadata)
└── setup.bat                    ✅ EXISTENT
```

**Tâches:**
- [ ] **1.1.1** Documenter chaque dossier (ajout de README.md ou commentaires)
- [ ] **1.1.2** Vérifier l'intégrité des imports et uses dans les fichiers Core
- [ ] **1.1.3** Créer `src/Debug/Debugger.php` pour logging avancé

---

## 🎨 PHASE 2: SYSTÈME DE TEMPLATE/LAYOUT/BLOCK

### 2.1 Implémenter Layout pour Admin
**Concept:** Les layouts définissent la structure HTML, les blocks le contenu, les templates l'affichage

**Fichiers à créer:**

#### 2.1.1 Layout XML - Admin Base
**Créer:** `themes/default/layouts/admin.xml`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <default>
        <block name="root" type="DataBuilder\Block\Page">
            <action method="setTemplate">
                <template>page/layout.phtml</template>
            </action>
            <block name="header" type="DataBuilder\Block\Header"/>
            <block name="content" type="DataBuilder\Block\Content"/>
            <block name="footer" type="DataBuilder\Block\Footer"/>
        </block>
    </default>
    
    <!-- Page spécifique: Admin -->
    <admin_index>
        <update handle="default"/>
        <reference name="content">
            <block name="admin.index" template="admin/index.phtml"/>
        </reference>
    </admin_index>
</layout>
```

**Tâches:**
- [ ] **2.1.1** Créer layout structure pour admin (ci-dessus)
- [ ] **2.1.2** Créer layout pour client area (similaire)
- [ ] **2.1.3** Créer layout pour reseller area
- [ ] **2.1.4** Ajouter layout pour pages d'erreur (404, 500, etc)

#### 2.1.2 Templates Phtml - Structure
**Créer:** `themes/default/templates/page/layout.phtml`

**Tâches:**
- [ ] **2.1.5** Créer `templates/page/layout.phtml` (layout principal)
- [ ] **2.1.6** Créer `templates/page/header.phtml` (header)
- [ ] **2.1.7** Créer `templates/page/footer.phtml` (footer)
- [ ] **2.1.8** Créer `templates/admin/index.phtml` (page admin index)

#### 2.1.3 Blocks PHP
**Créer:** `src/Block/Admin/IndexBlock.php`

**Tâches:**
- [ ] **2.1.9** Créer block pour admin index
- [ ] **2.1.10** Créer block pour header
- [ ] **2.1.11** Créer block pour footer
- [ ] **2.1.12** Implémenter logique de rendu des sous-blocks

---

## 📊 PHASE 3: SYSTÈME DE DONNÉES (DATA SEPARATION)

### 3.1 Structure de données XML
**Concept:** Séparer les données des templates pour modulabilité

**Créer:** `themes/default/data/admin/indexData.xml`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<data>
    <page name="admin_index" title="Admin Dashboard">
        <section name="users">
            <label>{TR_ADMIN_USERS}</label>
            <count>{ADMIN_USERS_COUNT}</count>
        </section>
        <section name="resellers">
            <label>{TR_RESELLER_USERS}</label>
            <count>{RESELLER_USERS_COUNT}</count>
        </section>
    </page>
</data>
```

**Tâches:**
- [ ] **3.1.1** Créer structure de répertoires pour données:
  ```
  themes/default/data/
  ├── admin/
  │   ├── indexData.xml
  │   ├── dashboard.xml
  │   ├── admin_log.xml
  │   └── ...
  ├── client/
  │   ├── indexData.xml
  │   └── ...
  └── reseller/
      ├── indexData.xml
      └── ...
  ```
- [ ] **3.1.2** Créer DataLoader class pour charger XML
- [ ] **3.1.3** Créer VariableResolver pour remplacer les placeholders
- [ ] **3.1.4** Implémenter caching des données XML

### 3.2 Système de variables dynamiques
**Tâches:**
- [ ] **3.2.1** Intégrer avec iMSCP pour obtenir les variables dynamiques
- [ ] **3.2.2** Créer un système de mappage (XML variable → iMSCP variable)
- [ ] **3.2.3** Implémenter accès sécurisé aux variables (pas de requête DB)

---

## 🔄 PHASE 4: SYSTÈME DE FALLBACK (PAGE ROUTING)

### 4.1 Routing dynamique avec fallback
**Concept:** DataBuilder essaye de rendre une page, sinon délègue à iMSCP

**Tâches:**
- [ ] **4.1.1** Améliorer ImscpPageController pour:
  - [ ] Détecter automatiquement page name depuis URL
  - [ ] Chercher layout: `themes/default/layouts/{page}.xml`
  - [ ] Chercher template: `themes/default/templates/{area}/{page}.phtml`
  - [ ] Si trouvé → Render avec DataBuilder
  - [ ] Sinon → Return null pour iMSCP
- [ ] **4.1.2** Implémenter Page Resolver (cherche dans base → default → custom)
- [ ] **4.1.3** Créer système de priorités de thème

### 4.2 Intégration avec iMSCP theme system
**Tâches:**
- [ ] **4.2.1** Créer hook pour intercepter chaque page iMSCP
- [ ] **4.2.2** Vérifier si DataBuilder a une version de la page
- [ ] **4.2.3** Implémenter fallback sans brisure UI

---

## 🎁 PHASE 5: CRÉATION DE LA PAGE ADMIN INDEX

### 5.1 Page Admin Index - Structure complète
**Objectif:** Créer une première page DataBuilder complète comme POC

#### 5.1.1 Layout pour Admin Index
**Créer/Modifier:** `themes/default/layouts/admin_index.xml`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <admin_index>
        <update handle="default"/>
        <reference name="content">
            <block name="admin.index" type="DataBuilder\Block\Admin\IndexBlock"
                   template="admin/index.phtml">
                <block name="users.section" type="DataBuilder\Block\Admin\UsersSection"/>
                <block name="resellers.section" type="DataBuilder\Block\Admin\ResellersSection"/>
                <block name="statistics" type="DataBuilder\Block\Admin\StatisticsBlock"/>
            </block>
        </reference>
    </admin_index>
</layout>
```

**Tâches:**
- [ ] **5.1.1** Créer layout admin_index.xml
- [ ] **5.1.2** Créer data file: `themes/default/data/admin/indexData.xml`
- [ ] **5.1.3** Créer template: `themes/default/templates/admin/index.phtml`
- [ ] **5.1.4** Créer block classes:
  - [ ] IndexBlock.php
  - [ ] UsersSection.php
  - [ ] ResellersSection.php
  - [ ] StatisticsBlock.php

#### 5.1.2 Template pour Admin Index
**Créer:** `themes/default/templates/admin/index.phtml`

**Tâches:**
- [ ] **5.1.5** Créer template HTML/CSS d'admin index
- [ ] **5.1.6** Implémenter rendu des blocks
- [ ] **5.1.7** Ajouter design responsive

#### 5.1.3 Données pour Admin Index
**Créer:** `themes/default/data/admin/indexData.xml`

**Tâches:**
- [ ] **5.1.8** Définir structure de données
- [ ] **5.1.9** Documenter les variables disponibles

#### 5.1.4 Blocks PHP
**Créer:** `src/Block/Admin/`

**Tâches:**
- [ ] **5.1.10** Implémenter IndexBlock
- [ ] **5.1.11** Implémenter UsersSection
- [ ] **5.1.12** Implémenter ResellersSection
- [ ] **5.1.13** Implémenter StatisticsBlock
- [ ] **5.1.14** Ajouter logique de rendu des enfants

---

## 🎨 PHASE 6: DESIGN & STYLING

### 6.1 CSS/SCSS Framework
**Tâches:**
- [ ] **6.1.1** Créer structure CSS:
  ```
  themes/default/assets/
  ├── css/
  │   ├── bootstrap.min.css (ou framework)
  │   ├── theme.css
  │   ├── admin.css
  │   └── components/
  │       ├── buttons.css
  │       ├── forms.css
  │       ├── tables.css
  │       └── cards.css
  └── js/
      ├── app.js
      └── components/
  ```
- [ ] **6.1.2** Créer design system (couleurs, typos, spacing)
- [ ] **6.1.3** Implémenter responsive design
- [ ] **6.1.4** Supporter light/dark mode via CSS variables

### 6.2 Dark Mode Support
**Tâches:**
- [ ] **6.2.1** Définir variables CSS pour light/dark
- [ ] **6.2.2** Créer `assets/css/dark-mode.css`
- [ ] **6.2.3** Implémenter toggle dark mode
- [ ] **6.2.4** Tester dark mode (fix du bug actuel)

---

## 🔌 PHASE 7: SYSTÈME D'INSTALLATION & HOOKS

### 7.1 Installation Hook
**Tâches:**
- [ ] **7.1.1** Implémenter `onInstall()` dans DataBuilderIMSCPBetaPlugin.php:
  - [ ] Copier thème dans `iMSCP/gui/themes/databuilder/`
  - [ ] Créer structure de répertoires
  - [ ] Créer fichiers de configuration
  - [ ] Initialiser base de données si besoin
- [ ] **7.1.2** Créer fichier SQL pour migrations
- [ ] **7.1.3** Vérifier permissions de fichiers

### 7.2 Uninstall Hook
**Tâches:**
- [ ] **7.2.1** Implémenter `onUninstall()`:
  - [ ] Supprimer thème de `iMSCP/gui/themes/databuilder/`
  - [ ] Nettoyer cache
  - [ ] Restaurer thème par défaut
  - [ ] Nettoyer configuration

### 7.3 Update Hook
**Tâches:**
- [ ] **7.3.1** Implémenter `onUpdate()`:
  - [ ] Migrer configuration
  - [ ] Mettre à jour thème
  - [ ] Vérifier compatibilité

---

## 👨‍💻 PHASE 8: DOCUMENTATION DÉVELOPPEUR

### 8.1 Guide de création de pages
**Créer:** `docs/DEVELOPER_GUIDE.md`

**Continu:**
```
# Guide Développeur DataBuilder

## 1. Créer une nouvelle page
1. Créer layout: `themes/custom/layouts/admin/mypage.xml`
2. Créer template: `themes/custom/templates/admin/mypage.phtml`
3. Créer data: `themes/custom/data/admin/mypageData.xml`
4. Créer blocks si besoin: `src/Block/Admin/MypageBlock.php`

## 2. Surcharger une page existante
- Mettre à jour fichier au même chemin dans `themes/custom/`
- DataBuilder utilise `custom/ > default/ > base/`

## 3. Paramètres de block
Tous les blocks supportent les paramètres:
- `template`: template file
- `data`: external XML data file
- `cache`: enable/disable cache
- `params`: paramètres additionnels
```

**Tâches:**
- [ ] **8.1.1** Créer DEVELOPER_GUIDE.md complet
- [ ] **8.1.2** Documenter API des blocks
- [ ] **8.1.3** Créer exemples de pages
- [ ] **8.1.4** Documenter système de donnéesXML

### 8.2 API Documentation
**Tâches:**
- [ ] **8.2.1** Documenter classes principales
- [ ] **8.2.2** Créer cheatsheet de fonctions
- [ ] **8.2.3** Ajouter commentaires PHPDoc

---

## 🧪 PHASE 9: TESTING & QA

### 9.1 Tests unitaires
**Tâches:**
- [ ] **9.1.1** Créer tests pour Core/Engine
- [ ] **9.1.2** Créer tests pour Layout/Block/Template
- [ ] **9.1.3** Créer tests pour Router
- [ ] **9.1.4** Créer tests pour DataLoader

### 9.2 Tests d'intégration
**Tâches:**
- [ ] **9.2.1** Tester `/admin/databuilder` charge correctement
- [ ] **9.2.2** Tester fallback vers iMSCP
- [ ] **9.2.3** Tester dark mode
- [ ] **9.2.4** Tester multi-page rendering
- [ ] **9.2.5** Tester override de fichiers

### 9.3 Tests de performance
**Tâches:**
- [ ] **9.3.1** Benchmark rendering page simple
- [ ] **9.3.2** Benchmark avec cache activé
- [ ] **9.3.3** Benchmark multi-block page
- [ ] **9.3.4** Profiler memory usage

---

## 🚀 PHASE 10: OPTIMISATION & POLISH

### 10.1 Performance
**Tâches:**
- [ ] **10.1.1** Implémenter caching efficace
- [ ] **10.1.2** Minifier CSS/JS
- [ ] **10.1.3** Lazy-load assets si besoin
- [ ] **10.1.4** Optimiser queries iMSCP

### 10.2 Sécurité
**Tâches:**
- [ ] **10.2.1** Valider inputs de formulaires
- [ ] **10.2.2** Échapper outputs correctement
- [ ] **10.2.3** Vérifier authentification/permissions
- [ ] **10.2.4** Implémenter CSRF protection
- [ ] **10.2.5** Valider XML files

### 10.3 Compatibilité
**Tâches:**
- [ ] **10.3.1** Tester sur iMSCP 1.5.x
- [ ] **10.3.2** Tester sur différents PHP versions
- [ ] **10.3.3** Tester sur différents navigateurs
- [ ] **10.3.4** Tester responsive design

---

## 📋 RÉCAPITULATIF DES FICHIERS À CRÉER/MODIFIER

### À CRÉER (Nouveau):
```
src/Debug/Debugger.php                          (⬜ NOUVEAU)
themes/default/layouts/admin.xml                (⬜ NOUVEAU)
themes/default/layouts/admin_index.xml          (⬜ NOUVEAU)
themes/default/templates/page/layout.phtml      (⬜ NOUVEAU)
themes/default/templates/page/header.phtml      (⬜ NOUVEAU)
themes/default/templates/page/footer.phtml      (⬜ NOUVEAU)
themes/default/templates/admin/index.phtml      (⬜ NOUVEAU)
themes/default/data/admin/indexData.xml         (⬜ NOUVEAU)
src/Block/Admin/IndexBlock.php                  (⬜ NOUVEAU)
src/Block/Admin/UsersSection.php                (⬜ NOUVEAU)
src/Block/Admin/ResellersSection.php            (⬜ NOUVEAU)
src/Block/Admin/StatisticsBlock.php             (⬜ NOUVEAU)
src/DataLoader.php                              (⬜ NOUVEAU)
src/VariableResolver.php                        (⬜ NOUVEAU)
themes/default/assets/css/theme.css             (⬜ NOUVEAU)
themes/default/assets/css/dark-mode.css         (⬜ NOUVEAU)
docs/DEVELOPER_GUIDE.md                         (⬜ NOUVEAU)
tests/unit/CoreEngineTest.php                   (⬜ NOUVEAU)
```

### À MODIFIER (Existants):
```
frontend/admin/databuilder.php                  (⚠️ FIX ERROR)
src/PageRenderer.php                            (✏️ AMÉLIORER)
src/Controller/ImscpPageController.php          (✏️ AMÉLIORER)
DataBuilderIMSCPBetaPlugin.php                  (✏️ HOOKS)
config.php                                      (✏️ AMÉLIORER)
```

---

## 🎯 PRIORITÉS D'IMPLÉMENTATION

### CRITICAL (P0) - FAIRE EN PREMIER
1. **0.1** Fix dark mode error `/admin/databuilder` ⚠️
2. **5.1** Créer page admin index complete (première page de démo)
3. **6.1 & 6.2** Design basic + dark mode support
4. **4.1** Système de fallback vers iMSCP
5. **7.1** Hook d'installation plugin

### IMPORTANT (P1) - FAIRE APRÈS
6. **2.1** Layout system complet
7. **3.1** Data separation system
8. **8.1** Documentation développeur
9. **9.1 & 9.2** Tests unitaires & intégration

### NICE-TO-HAVE (P2)
10. **10.1** Optimisation performance
11. **10.2** Sécurité avancée
12. **9.3** Tests de performance
13. **8.2** API documentation complète

---

## ✅ CHECKLIST DE VALIDATION

Une fois complet, le plugin doit:

- [ ] Installer sans erreurs
- [ ] `/admin/databuilder` charge sans erreur
- [ ] Admin index affiche avec design correct
- [ ] Dark mode fonctionne
- [ ] Pages non-DataBuilder délèguent à iMSCP
- [ ] Permet création de nouvelles pages
- [ ] Permet override de fichiers
- [ ] Pas d'erreurs PHP
- [ ] Performance acceptable (<100ms rendering)
- [ ] Documentation clara pour développeurs
- [ ] Peut être installé/désinstallé sans résidus

---

## 📞 NOTES IMPORTANTES

**RAPPELS CONSTANTS:**
1. ❌ Ne JAMAIS toucher `/imscp` folder
2. ❌ DataBuilder n'effectue JAMAIS de requête DB
3. ✅ Toute logique métier vient d'iMSCP (via variables)
4. ✅ DataBuilder = ENGINE DE RENDU UNIQUEMENT
5. ✅ Modulaire et flexible (surcharge sans modification source)

**Architecture rappel:**
```
iMSCP Core
    ↓
Plugin DataBuilder (intercepte pages)
    ↓
DataBuilder Has Page?
    ├─ OUI → Render avec DataBuilder (layout/block/template)
    └─ NON → Déléguer à iMSCP (fallback)
```

---

## 🔗 RÉFÉRENCES

- TODO_IMPLEMENTATION.md - Document technique détaillé
- TODO_ADMIN_INDEX.md - État actuel de la page admin
- README.md plugin - Info générale

**Fin du plan.**
