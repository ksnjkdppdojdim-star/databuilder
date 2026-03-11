# themes/custom/client/

Templates visuels des pages **client**. Un dossier par page, un fichier `.phtml` par bloc.

## Structure

```
themes/custom/client/
└── nom_de_la_page/
    └── main.phtml
```

## Dans un .phtml — lire les données

```php
<?php
$valeur = $block->getData('CLE', 'valeur_par_defaut');
?>
<div><?= htmlspecialchars($valeur) ?></div>
```

## Règles

- Toujours échapper les données affichées : `htmlspecialchars()`
- Ne pas faire de requêtes BDD directement ici — tout passe par le controller
