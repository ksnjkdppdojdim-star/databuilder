# themes/custom/layouts/admin/

Un fichier XML par page **admin**. Le nom du fichier doit correspondre exactement au `name` dans `config/admin/pages.xml`.

## Structure XML minimale

```xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <handle name="admin/ma_page">
        <block type="DataBuilder\Block\TemplateBlock"
               name="ma_page.main"
               template="themes/custom/admin/ma_page/main.phtml"/>
    </block>
    </handle>
</layout>
```

## Règles

- `handle name` = `admin/{nom_de_la_page}`
- Un bloc = un fichier `.phtml` dans `themes/custom/admin/{nom_de_la_page}/`
- Ajouter autant de blocs que nécessaire (en-tête, contenu, pied de page...)

➡ Voir `AJOUTER_UNE_PAGE.md` à la racine pour le guide complet.
