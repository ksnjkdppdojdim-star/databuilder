# 🔧 DataBuilder MVP1 - Guide de Debugging

## 📋 Checklist de Test

### 1. **Variables iMSCP s'affichent textuellement**
Si vous voyez `{PAGE_TITLE}`, `{SIDEBAR_NAVIGATION}`, etc. sur la page:

**Cause probable:**
- iMSCP replace les variables AVANT que DataBuilder ne rende la page
- Le wrapper `ui.tpl` de iMSCP ne s'applique pas correctement

**Vérification:**
```
Ouvrir l'inspecteur (F12) → Onglet "Network"
Charger la page http://your-imscp:port/admin/index
Voir la réponse HTML complète - regarde les variables non remplacées
```

**Solution:**
Les variables doivent être remplacées par iMSCP **avant** d'arriver à DataBuilder.
Cela signifie que `themes/admin/index.tpl` doit être appelé **depuis** `ui.tpl` (wrapper iMSCP).

### 2. **Pas de CSS/Design**

**Causes probables:**
1. Les fichiers CSS ne sont pas chargés
2. Le chemin des assets est incorrect
3. iMSCP n'injecte pas les fichiers CSS

**Vérification:**
```javascript
// Ouvrir F12 → Console et taper:
window.DataBuilderDebug.getColors()
// Si undefined, le JS n'est pas chargé
```

**Vérification avancée:**
```
F12 → Onglet "Elements" → Chercher <head>
Chercher: <link rel="stylesheet" href=".../databuilder.css">
Si absent → CSS non chargé
```

**Solution:**
Vérifier que:
1. `themes/default/css/databuilder.css` existe
2. Les assets sont injectés correctement dans le HTML
3. Les chemins sont relatifs ou absolus corrects

### 3. **Les données s'affichent mais pas le design**

**Cause:** CSS non appliqué, mais données présentes

**Vérification:**
```javascript
// F12 → Console → Taper:
document.querySelectorAll('.stat-card').length
// Doit retourner >= 8 (les 8 cartes)

// Vérifier les styles appliqués:
document.querySelector('.stat-card').getAttribute('style')
// Doit être vide (styles viennent du CSS externe)

// Vérifier le CSS:
window.getComputedStyle(document.querySelector('.stat-card')).display
// Doit retourner 'flex'
```

### 4. **Erreurs JavaScript**

**Vérification:**
```
F12 → Onglet "Console"
Regarder les erreurs rouges
```

**Erreurs courantes:**
- `ReferenceError: DataBuilderDebug is not defined` → JS pas chargé
- `Uncaught TypeError: Cannot read property 'getStatistics' of undefined` → Block non initialisé
- Erreurs de chemin CORS → Assets sur servlet different (vérifier Access-Control-Allow-Origin)

### 5. **Fichiers CSS/JS introuvables**

**Vérification (dans le terminal des logs server):**
```bash
# Chercher les erreurs 404
grep "404" /var/log/apache2/error.log | grep databuilder
grep "404" /var/log/nginx/error.log | grep databuilder
```

**Vérifier les chemins:**
```bash
# Les fichiers existent-ils?
ls -la c:\Users\Jules\Documents\iMSCP\databuilder\themes\default\css\
ls -la c:\Users\Jules\Documents\iMSCP\databuilder\themes\default\js\
```

## 📝 Points de contrôle dans le Code

### 1. **Vérifier que fonts Material Design se charge**
```javascript
// F12 → Console → Taper:
document.querySelector('.material-symbols-outlined')?.offsetWidth > 0
// Doit retourner true (sinon font pas chargée)
```

### 2. **Vérifier le système des variables**
```javascript
// F12 → Console → Taper:
window.DataBuilderDebug?.getStats()
// Doit montrer les 8 variables (ADMIN_USERS, etc.)
```

### 3. **Vérifier que iMSCP remplace ses variables**
```bash
Souris droite sur la page → Afficher le source HTML
Chercher {TR_ADMIN_USERS} - ne doit pas être là
Doit afficher le texte traduit: "Administrateurs", "Revendeurs", etc.
```

## 🔍 Debugging Avancé (mode développement)

### 1. **Activer le mode debug DataBuilder**
```php
// Ajouter dans themes/admin/index.tpl
$_ENV['DATABUILDER_DEBUG'] = true;
```

### 2. **Inspectionner les CSS variables**
```javascript
// F12 → Console:
const style = getComputedStyle(document.documentElement);
const vars = ['--db-primary', '--db-spacing-lg', '--db-radius-md'];
vars.forEach(v => console.log(`${v}: ${style.getPropertyValue(v)}`));
```

### 3. **Tester le responsive design**
```javascript
// F12 → Console:
// Simuler width = 768px (break point)
window.innerWidth
// Puis tester le CSS media query

// Ou faire: F12 → Burger menu → Responsive Design Mode (Ctrl+Shift+M)
```

### 4. **Vérifier l'héritage des thèmes**
```bash
# Vérifier l'ordre de recherche:
1. themes/custom/  → Priorité 1 (user customisations)
2. themes/default/ → Priorité 2 (default assets)
3. themes/base/    → Priorité 3 (fallback)
```

## 📊 Structure attendue après chaque étape

### **Après le chargement de la page:**

1. ✅ HTML avec divs `.stat-card` et `.traffic-progress` présents
2. ✅ Chiffres correctement affichés (8, 2, 4, etc.)
3. ✅ Les `<link>` et `<script>` présents dans `<head>` et avant `</body>`
4. ✅ Les CSS colors/layout appliqués (pas textuellement empilés)
5. ✅ Les Material Design icons visibles

### **Après le click sur une carte (si implémenté):**

1. ✅ Animation translateY
2. ✅ Changement de couleur border
3. ✅ Optionnel: navigation vers la page correspondante

## 🎯 Résumé rapide du debugging

| Problème | Vérification | Solution |
|----------|-------------|----------|
| Variables s'affichent | Afficher source → F12 → Chercher `{TR_` | Vérifier ui.tpl de iMSCP appliqué |
| Pas de CSS | F12 → Network → Chercher `.css 200/404` | Vérifier chemin des assets |
| Pas de JS | F12 → Console → `window.DataBuilderDebug` | Vérifier injection des JS |
| Icons invisibles | F12 → Elements → Material Sans charged | Charger Material Design Icons |
| Grille horizontale | F12 → Computed → `grid-template-columns` | Vérifier media queries CSS |
| Variables textuelles | Afficher source → Chercher `{ADMIN_USERS}` | Doit être remplacé par le chiffre |

---

## 📬 Retour au Développeur

Quand vous rapportez un bug, incluez:

```
**Environnement:**
- Version iMSCP: [version]
- Navigateur: [Chrome/Firefox/Safari]
- OS: Windows/Linux

**Screenshots:**
[Capture d'écran de la page]

**Console F12:** (F12 → Console → Copier les erreurs)
[Erreurs affichées]

**HTML Source** (Ctrl+U ou click droit → Afficher source):
[Chercher {TR_ ou {PAGE_TITLE} ou {SIDEBAR - copier 20 lignes]

**Problème:**
[Description claire et concise]
```

Cela nous aidera à identifier rapidement le problème! 🎯
