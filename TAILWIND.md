# Tailwind CSS dans le Projet

## Installation et Configuration

Le projet utilise Tailwind CSS pour tout le styling avec une approche "dark mode" par défaut.

### Couleurs Personnalisées

Les couleurs du thème sont définies dans `tailwind.config.js` :

- `bg-main` : #121212 (fond principal)
- `bg-surface` : #1E1E1E (surfaces/cartes)
- `bg-hover` : #282828 (état hover)
- `primary` : #1DB954 (vert Spotify-like)
- `primary-hover` : #1ed760
- `text-main` : #FFFFFF (texte principal)
- `text-sub` : #B3B3B3 (texte secondaire)
- `border-main` : #333333

### Utilisation

Dans vos vues PHP, utilisez directement les classes Tailwind :

```php
<div class="bg-bg-surface rounded-lg p-4 hover:bg-bg-hover transition-colors">
    <h3 class="text-text-main font-semibold"><?= $title ?></h3>
    <p class="text-text-sub text-sm"><?= $description ?></p>
</div>
```

### Compilation

**Mode développement** (watch) :
```bash
npm run watch
```

**Mode production** (build) :
```bash
npm run build
```

Le fichier CSS compilé est généré dans `/public/assets/css/output.css`.

### Composants Tailwind Personnalisés

Des classes utilitaires custom sont définies dans `src/input.css` :

- `.custom-scrollbar` : Scrollbar stylisée
- `.hide-scrollbar` : Cache la scrollbar tout en gardant le scroll

### Spacing Personnalisés

- `h-player` : 90px (hauteur du lecteur)
- `h-nav` : 60px (hauteur de la navigation)
- `h-header` : 60px (hauteur du header)
- `pt-header` : Padding top équivalent au header
- `pb-[calc(...)]` : Padding bottom pour navigation + lecteur

### Exemples de Patterns

#### Liste de tracks (style row)

```php
<div class="flex items-center px-4 py-2.5 hover:bg-border-main">
    <img src="<?= $cover ?>" class="w-12 h-12 rounded object-cover mr-3">
    <div class="flex-1 min-w-0">
        <div class="text-base truncate"><?= $title ?></div>
        <div class="text-sm text-text-sub truncate"><?= $artist ?></div>
    </div>
</div>
```

#### Bouton primaire

```php
<button class="px-5 py-2.5 rounded-full bg-primary text-white font-semibold hover:bg-primary-hover transition-colors">
    Action
</button>
```

#### Modal

```php
<div class="fixed inset-0 bg-black/80 z-[1000] flex items-center justify-center">
    <div class="bg-bg-surface p-8 rounded-xl max-w-md w-11/12">
        <!-- Contenu -->
    </div>
</div>
```

### Breakpoints Tailwind

- `sm:` : 640px
- `md:` : 768px
- `lg:` : 1024px
- `xl:` : 1280px
- `2xl:` : 1536px

Exemple : `hidden md:block` (caché sur mobile, visible sur desktop)

## Notes Importantes

1. **Ne pas créer de CSS custom** : Utilisez toujours les classes Tailwind
2. **Recompiler après modifications** : Lancez `npm run build` ou `watch`
3. **PurgeCSS activé** : Seules les classes utilisées sont incluses en production
4. **Dark mode natif** : Toutes les couleurs sont adaptées au mode sombre

## Design System

L'application suit un design minimaliste et moderne :

- Bordures arrondies (`rounded-lg`, `rounded-full`)
- Transitions douces (`transition-colors`, `transition-all`)
- Effets hover subtils
- Espacements cohérents (multiples de 4)
- Typographie responsive
