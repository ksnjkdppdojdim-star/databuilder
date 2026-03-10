# config/client/pages.xml

Registre de toutes les pages **client** gérées par DataBuilder.

## Pour ajouter une page client

Ajouter une ligne dans ce fichier :

```xml
<page name="nom_de_la_page"
      controller="DataBuilder\Controller\Client\NomController"/>
```

- `name` = nom du script PHP sans extension (ex : `index` pour `/client/index.php`)
- `controller` = nom complet de la classe PHP

## Fichiers à créer ensuite

1. `src/Controller/Client/NomController.php`
2. `themes/custom/layouts/client/nom_de_la_page.xml`
3. `themes/custom/client/nom_de_la_page/*.phtml`
4. `themes/client/nom_de_la_page.tpl` (stub vide)

➡ Voir `AJOUTER_UNE_PAGE.md` à la racine du plugin pour le guide complet.
