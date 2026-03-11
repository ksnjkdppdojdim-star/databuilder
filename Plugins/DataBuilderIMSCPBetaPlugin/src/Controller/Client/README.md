# src/Controller/Client/

Un fichier PHP par page **client**.

## Namespace

```php
namespace DataBuilder\Controller\Client;
```

## Squelette minimal

```php
<?php
namespace DataBuilder\Controller\Client;

use DataBuilder\Controller\AbstractController;

class MaPageController extends AbstractController
{
    public function execute(): void
    {
        $this->loadLayout();          // charge themes/custom/layouts/client/ma_page.xml
        $this->propagateData();
        $this->renderLayout();
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
- `loadLayout()` sans argument = utilise automatiquement le handle `client/nom_page`

➡ Voir `AJOUTER_UNE_PAGE.md` à la racine pour le guide complet.
