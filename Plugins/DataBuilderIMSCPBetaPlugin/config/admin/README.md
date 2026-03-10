# config/admin/pages.xml

Registre de toutes les pages **admin** gérées par DataBuilder.

## Pour ajouter une page admin

Ajouter une ligne dans ce fichier :

```xml
<page name="nom_de_la_page"
      controller="DataBuilder\Controller\Admin\NomController"/>
```

- `name` = nom du script PHP sans extension (ex : `admin_log` pour `/admin/admin_log.php`)
- `controller` = nom complet de la classe PHP

C'est **tout**. Le plugin lit ce fichier automatiquement — rien d'autre à modifier dans le plugin.

## Fichiers à créer ensuite

1. `src/Controller/Admin/NomController.php`
2. `themes/custom/layouts/admin/nom_de_la_page.xml`
3. `themes/custom/admin/nom_de_la_page/*.phtml`
4. `themes/admin/nom_de_la_page.tpl` (stub vide)

➡ Voir `AJOUTER_UNE_PAGE.md` à la racine du plugin pour le guide complet.
