# Music Streaming App - Nouvelles Fonctionnalités

## 🎨 Améliorations v3.0

### 1. Player Bar Responsive ✅
- **Texte tronqué** : Les titres et artistes longs sont automatiquement tronqués avec `...`
- **Contrôles adaptatifs** : Taille réduite sur mobile (`sm:` breakpoints)
- **Espacement optimisé** : `gap-2` sur mobile, `gap-5` sur desktop
- **Bouton Like** : Ajouté directement dans la player bar

### 2. Système de Téléchargement Amélioré ✅
- **Spinner au clic** : Animation de chargement immédiate
- **File d'attente** : Panel latéral affichant tous les téléchargements
- **Barre de progression** : Indicateur visuel pour chaque téléchargement
- **Queue automatique** : Les téléchargements sont traités séquentiellement

#### Utilisation
```javascript
// Ajouter un téléchargement à la queue
addToDownloadQueue(videoId, title, customTitle);

// Afficher/masquer la queue
toggleDownloadQueue();
```

### 3. Modales Personnalisées ✅
Remplacement des `prompt()` et `alert()` natifs par des modales élégantes.

#### Sélection de Playlist
- Liste visuelle avec pochettes
- Sélection par clic (bordure bleue)
- Validation avant ajout

#### Créer une Playlist
- Modal avec input stylisé
- Focus automatique sur l'input
- Validation du nom

#### Utilisation
```javascript
// Ouvrir la modal de sélection de playlist
showPlaylistModal(songId);

// Créer une nouvelle playlist
showCreatePlaylistModal();

// Modal personnalisée
openModal('Titre', 'Contenu HTML', [
    {
        text: 'Confirmer',
        className: 'px-4 py-2 bg-primary text-white rounded-lg',
        onClick: () => { /* action */ }
    }
]);
```

### 4. Système de Likes (Favoris) ✅

#### Base de données
Nouvelle table `liked_songs` :
```sql
CREATE TABLE liked_songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    song_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (song_id),
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);
```

#### Fonctionnalités
- **Icône cœur** sur chaque carte de musique
- **Bouton like** dans la player bar
- **Page dédiée** `/liked` accessible depuis la navigation
- **Toggle rapide** : Clic = like/unlike immédiat
- **État synchronisé** : Les boutons se mettent à jour automatiquement

#### API
- `POST /like/{id}/toggle` - Ajouter/retirer des favoris
- `GET /like/{id}/check` - Vérifier le statut like
- `GET /liked` - Page des titres likés

#### Utilisation
```javascript
// Toggle like
toggleLike(songId, buttonElement);

// Vérifier le statut
checkLikeStatus(songId);

// Mise à jour automatique au changement de chanson
```

## 📱 Responsive Design

### Breakpoints
- **Mobile** : < 640px (base)
- **Tablet** : >= 640px (`sm:`)
- **Desktop** : >= 1024px (`lg:`)

### Player Bar
```html
<!-- Mobile -->
<div class="px-2 gap-2">
  <img class="w-10 h-10">
  <button class="w-7 h-7">
  
<!-- Desktop -->  
<div class="sm:px-5 sm:gap-5">
  <img class="sm:w-14 sm:h-14">
  <button class="sm:w-9 sm:h-9">
```

### Navigation
5 onglets compacts avec icônes réduites sur mobile

## 🚀 Migration

### Appliquer les nouvelles tables
```powershell
# Windows PowerShell
.\migrate.ps1

# Ou manuellement
mysql -u root -p music_streaming < database_likes.sql
```

## 📦 Fichiers Créés/Modifiés

### Nouveaux fichiers
- `src/Models/Like.php` - Modèle pour les likes
- `src/Controllers/LikeController.php` - Contrôleur likes
- `src/Views/pages/liked.php` - Page des titres likés
- `src/Views/components/modal.php` - Composant modal réutilisable
- `public/assets/js/modal.js` - Gestion des modales
- `public/assets/js/download.js` - Queue de téléchargement
- `database_likes.sql` - Migration SQL
- `migrate.ps1` - Script de migration

### Fichiers modifiés
- `src/Views/components/player-bar.php` - Responsive + bouton like
- `src/Views/components/navigation.php` - Ajout onglet "Liked"
- `src/Views/components/music-card.php` - Boutons like et playlist
- `src/Views/layouts/main.php` - Inclusion modal et scripts
- `src/Views/pages/search.php` - Download avec queue
- `public/assets/js/app.js` - Fonctions like
- `public/assets/js/player.js` - Vérification like au changement
- `public/index.php` - Routes likes

## 🎯 Notifications

Système de notifications toast :
```javascript
showNotification('Message', 'success'); // Vert
showNotification('Erreur', 'error');    // Rouge
showNotification('Info', 'info');       // Neutre
```

Auto-dismiss après 3 secondes, animation slide-in depuis la droite.

## 🔧 Configuration

Aucune configuration supplémentaire requise. Le système fonctionne out-of-the-box après la migration SQL.

## 🐛 Debug

En cas de problème :
1. Vérifier la migration SQL appliquée
2. Vider le cache du navigateur
3. Recompiler Tailwind : `npm run build`
4. Vérifier la console navigateur (F12)
