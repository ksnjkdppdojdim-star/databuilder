# src/Controller/Admin/

Un fichier PHP par page **admin**.

## Namespace

```php
namespace DataBuilder\Controller\Admin;
```

## Squelette minimal

```php
<?php
namespace DataBuilder\Controller\Admin;

use DataBuilder\Controller\AbstractController;

class MaPageController extends AbstractController
{
    public function execute(): void
    {
        $this->loadLayout();          // charge themes/custom/layouts/admin/ma_page.xml
        $this->propagateData();       // envoie les données aux blocs
        $this->renderLayout();        // affiche la page
    }

    private function propagateData(): void
    {
        // $block->setData('CLE', valeur);
    }
}
```

## Règles

- Nom du fichier = `NomController.php` (PascalCase)
- Doit étendre `AbstractController`
- `loadLayout()` sans argument = utilise automatiquement le handle `admin/nom_page`

➡ Voir `AJOUTER_UNE_PAGE.md` à la racine pour le guide complet.
