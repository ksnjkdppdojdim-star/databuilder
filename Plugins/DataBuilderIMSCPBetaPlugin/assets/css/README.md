# assets/css/

Feuilles de style de DataBuilder.

## Fichiers

| Fichier | Rôle |
|---------|------|
| `theme.css` | Design system complet — ne pas modifier directement |
| `theme-vars.css` | Surcharges optionnelles par thème — modifier ici |

## Personnaliser l'apparence

Ouvrir `theme-vars.css` et décommenter les variables à changer :

```css
:root {
    --db-p: #votre-couleur;       /* couleur principale */
    --db-sb-bg: #votre-couleur;   /* fond sidebar */
}
```

## Variables principales

| Variable | Défaut | Description |
|----------|--------|-------------|
| `--db-p` | `#2563eb` | Couleur principale (bleu) |
| `--db-p-dk` | `#1d4ed8` | Couleur principale foncée (hover) |
| `--db-sb-bg` | `#1e293b` | Fond de la sidebar |
| `--db-sb-txt` | `#cbd5e1` | Texte sidebar |
| `--db-bg` | `#f1f5f9` | Fond de page |
| `--db-cr` | `6px` | Rayon des coins des cartes |

> **Ne jamais modifier `theme.css`** — toutes les personnalisations vont dans `theme-vars.css`.
