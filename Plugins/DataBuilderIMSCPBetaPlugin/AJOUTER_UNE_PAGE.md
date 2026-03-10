# Ajouter une nouvelle page dans DataBuilder

## Résumé — 5 fichiers à créer, 1 ligne à ajouter

```
config/admin/pages.xml           ← 1 ligne à ajouter
src/Controller/Admin/            ← 1 fichier PHP à créer
themes/custom/layouts/admin/     ← 1 fichier XML à créer
themes/custom/admin/             ← 1 dossier de templates .phtml à créer
themes/admin/                    ← 1 fichier stub .tpl à créer
```

---

## Exemple concret : ajouter la page `admin_log`

### Étape 1 — Déclarer la page dans le registre

Ouvrir `config/admin/pages.xml` et ajouter une ligne :

```xml
<page name="admin_log"
      controller="DataBuilder\Controller\Admin\AdminLogController"/>
```

➡ C'est le **seul endroit** où on enregistre la page. Le plugin lit ce fichier automatiquement.

---

### Étape 2 — Créer le controller

Créer `src/Controller/Admin/AdminLogController.php` :

```php
<?php
namespace DataBuilder\Controller\Admin;

use DataBuilder\Controller\AbstractController;

class AdminLogController extends AbstractController
{
    public function execute(): string
    {
        $data      = $this->chargerDonnees();
        $rootBlock = $this->loadLayout();
        $this->diffuserDonnees($rootBlock, $data);
        return $rootBlock->render();
    }

    private function chargerDonnees(): array
    {
        $tpl = $this->registry->get('imscp_tpl_data') ?: [];
        $v = fn($k, $d = '') => ($tpl[$k] ?? '') !== '' ? $tpl[$k] : $d;

        return [
            'TR_DATE'    => $v('TR_DATE',    'Date'),
            'TR_MESSAGE' => $v('TR_MESSAGE', 'Message'),
            'LOG_ROWS'   => $v('LOG_ENTRIES', ''),
            // ... ajouter les clés iMSCP dont on a besoin
        ];
    }

    private function diffuserDonnees($block, array $data): void
    {
        if ($block instanceof \DataBuilder\Block\AbstractBlock) {
            $block->assignData($data);
        }
        foreach ($block->getChildren() as $child) {
            $this->diffuserDonnees($child, $data);
        }
    }
}
```

> **Astuce** : pour trouver les clés iMSCP disponibles, ajouter temporairement dans `chargerDonnees()` :
> ```php
> error_log('KEYS: ' . implode(', ', array_keys($tpl)));
> ```
> puis lire le log Apache/PHP.

---

### Étape 3 — Créer le layout XML

Créer `themes/custom/layouts/admin/admin_log.xml` :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <block name="root" class="DataBuilder\Block\ContainerBlock"
           htmlTag="div" htmlClass="databuilder-admin-log">

        <block name="header"
               class="DataBuilder\Block\ContainerBlock"
               template="admin/admin_log/header.phtml"/>

        <block name="log_table"
               class="DataBuilder\Block\ContainerBlock"
               template="admin/admin_log/log_table.phtml"/>

    </block>
</layout>
```

Le nom dans `template="admin/admin_log/..."` doit correspondre au dossier créé à l'étape 4.

---

### Étape 4 — Créer les templates

Créer le dossier `themes/custom/admin/admin_log/` avec autant de fichiers que de blocs dans le XML.

**`header.phtml`** :
```php
<?php $title = $block->getData('TR_ADMIN_LOG', 'Admin Log'); ?>
<div class="page-header">
    <h1><?= $block->e($title) ?></h1>
</div>
```

**`log_table.phtml`** :
```php
<?php
$tr_date    = $block->getData('TR_DATE', 'Date');
$tr_message = $block->getData('TR_MESSAGE', 'Message');
$log_rows   = $block->getData('LOG_ROWS', '');
?>
<div class="stats-table-container">
    <table class="stats-table">
        <thead>
            <tr>
                <th><?= $block->e($tr_date) ?></th>
                <th><?= $block->e($tr_message) ?></th>
            </tr>
        </thead>
        <tbody>
            <?= $log_rows ?>
        </tbody>
    </table>
</div>
```

---

### Étape 5 — Créer le stub iMSCP

Créer `themes/admin/admin_log.tpl` :

```
<!-- DataBuilder managed page -->
```

Ce fichier doit **exister** pour qu'iMSCP ne plante pas. Le contenu n'a aucune importance — DataBuilder remplace tout.

---

### Étape 6 — CSS (si besoin)

Si la page a de nouveaux composants visuels, ajouter les styles à la fin de `assets/css/theme.css`.

---

## Pour une page reseller ou client

Même processus, juste changer le scope :

| Ce qui change     | admin                              | reseller                               | client                               |
|-------------------|------------------------------------|----------------------------------------|--------------------------------------|
| Config            | `config/admin/pages.xml`           | `config/reseller/pages.xml`            | `config/client/pages.xml`            |
| Controller        | `src/Controller/Admin/`            | `src/Controller/Reseller/`             | `src/Controller/Client/`             |
| Layout XML        | `themes/custom/layouts/admin/`     | `themes/custom/layouts/reseller/`      | `themes/custom/layouts/client/`      |
| Templates         | `themes/custom/admin/`             | `themes/custom/reseller/`              | `themes/custom/client/`              |
| Stub iMSCP        | `themes/admin/nom_page.tpl`        | `themes/reseller/nom_page.tpl`         | `themes/client/nom_page.tpl`         |
| Namespace PHP     | `DataBuilder\Controller\Admin\`    | `DataBuilder\Controller\Reseller\`     | `DataBuilder\Controller\Client\`     |

---

## Ce qu'on ne touche JAMAIS

- `DataBuilderIMSCPBetaPlugin.php` — le plugin principal. Il lit `config/*/pages.xml` automatiquement.
- `src/Controller/AbstractController.php` — la classe de base.
- `themes/shared/layouts/ui.tpl` — la structure HTML globale.
- `assets/css/theme.css` — sauf pour ajouter des styles.
