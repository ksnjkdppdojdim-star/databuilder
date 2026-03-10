# themes/custom/admin/

Templates visuels des pages **admin**. Un dossier par page, un fichier `.phtml` par bloc.

## Structure

```
themes/custom/admin/
└── nom_de_la_page/
    ├── main.phtml      ← contenu principal
    ├── header.phtml    ← optionnel
    └── footer.phtml    ← optionnel
```

## Dans un .phtml — lire les données

```php
<?php
// Données envoyées par le controller via $block->setData('CLE', valeur)
$valeur = $block->getData('CLE', 'valeur_par_defaut');
?>
<div><?= htmlspecialchars($valeur) ?></div>
```

## Règles

- Toujours échapper les données affichées : `htmlspecialchars()`
- Ne pas faire de requêtes BDD directement ici — tout passe par le controller
- Le nom du dossier doit correspondre au `name` dans `config/admin/pages.xml`
