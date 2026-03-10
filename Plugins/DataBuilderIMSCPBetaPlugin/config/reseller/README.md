# config/reseller/pages.xml

Registre de toutes les pages **reseller** gérées par DataBuilder.

## Pour ajouter une page reseller

Ajouter une ligne dans ce fichier :

```xml
<page name="nom_de_la_page"
      controller="DataBuilder\Controller\Reseller\NomController"/>
```

- `name` = nom du script PHP sans extension (ex : `index` pour `/reseller/index.php`)
- `controller` = nom complet de la classe PHP

## Fichiers à créer ensuite

1. `src/Controller/Reseller/NomController.php`
2. `themes/custom/layouts/reseller/nom_de_la_page.xml`
3. `themes/custom/reseller/nom_de_la_page/*.phtml`
4. `themes/reseller/nom_de_la_page.tpl` (stub vide)

➡ Voir `AJOUTER_UNE_PAGE.md` à la racine du plugin pour le guide complet.
