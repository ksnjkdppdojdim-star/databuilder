# themes/custom/layouts/reseller/

Un fichier XML par page **reseller**.

## Structure XML minimale

```xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <handle name="reseller/ma_page">
        <block type="DataBuilder\Block\TemplateBlock"
               name="ma_page.main"
               template="themes/custom/reseller/ma_page/main.phtml"/>
    </handle>
</layout>
```

## Règles

- `handle name` = `reseller/{nom_de_la_page}`
- Un bloc = un fichier `.phtml` dans `themes/custom/reseller/{nom_de_la_page}/`

➡ Voir `AJOUTER_UNE_PAGE.md` à la racine pour le guide complet.
