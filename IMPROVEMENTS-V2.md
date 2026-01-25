# Améliorations Version 2.0

Ce document décrit les améliorations majeures apportées au projet pour corriger les points aveugles identifiés.

## ✅ 1. Métadonnées Réelles avec getID3

**Problème initial :** Les fichiers scannés affichaient "Unknown Artist" et durée = 0.

**Solution implémentée :**
- Installation de la bibliothèque `james-heinrich/getid3`
- Réécriture complète de `Music::extractMetadata()` pour lire les vrais tags ID3v1/ID3v2
- Extraction de : titre, artiste, album, durée, bitrate, sample_rate
- Fallback automatique sur le nom de fichier si tags absents
- Gestion d'erreurs avec logs

**Fichiers modifiés :**
- `composer.json` - ajout dépendance getID3
- `src/Models/Music.php` - nouvelle implémentation extractMetadata()

**Utilisation :**
```php
$metadata = $this->musicModel->extractMetadata('/path/to/song.mp3');
// Retourne : ['title', 'artist', 'album', 'duration', 'bitrate', 'sample_rate', 'format']
```

---

## ✅ 2. Gestion des Pochettes d'Album (Cover Art)

**Problème initial :** Les covers YouTube étaient embeddées dans le MP3 mais invisibles dans le navigateur.

**Solution implémentée :**

### A. Pour les téléchargements YouTube
- Ajout de `--write-thumbnail --convert-thumbnails jpg` à yt-dlp
- Extraction automatique du thumbnail en fichier JPG séparé
- Déplacement vers `/public/assets/images/covers/`
- Sauvegarde du chemin dans la BDD (`cover_path`)

### B. Pour les fichiers existants (scan)
- Nouvelle méthode `Music::extractCoverArt()` utilisant getID3
- Extraction des images APIC depuis les tags ID3v2
- Support PNG/JPG avec conversion automatique
- Génération de nom unique (md5 du filepath)

**Fichiers modifiés :**
- `src/Models/YoutubeDownloader.php` - download() avec extraction cover
- `src/Models/Music.php` - nouvelle méthode extractCoverArt()
- `src/Controllers/MusicController.php` - scan() avec covers
- `src/Controllers/SearchController.php` - download() avec cover_path

**Structure créée :**
```
public/assets/images/covers/
├── a3f2e8b9c1d4...jpg  (hash MD5 du fichier source)
├── 7b9c4e1f2a8d...jpg
└── ...
```

---

## ✅ 3. MediaSession API pour Contrôles Mobiles

**Problème initial :** La lecture s'arrêtait quand l'écran s'éteignait sur mobile.

**Solution implémentée :**
- Intégration complète de l'API `navigator.mediaSession`
- Affichage des contrôles sur l'écran verrouillé (iOS/Android)
- Support des actions : play, pause, previous, next, seekbackward, seekforward, seekto
- Métadonnées enrichies (titre, artiste, album, pochette)

**Fichiers modifiés :**
- `public/assets/js/player.js` - nouvelles méthodes initMediaSession() et updateMediaSession()

**Fonctionnalités :**
- ✅ Contrôles sur écran verrouillé
- ✅ Notification persistante avec pochette
- ✅ Boutons lecture/pause/suivant/précédent
- ✅ Barre de progression (seekto)
- ✅ Skip +/- 10 secondes

**Exemple d'affichage mobile :**
```
┌─────────────────────────┐
│   [Pochette d'album]    │
│                         │
│   Titre de la chanson   │
│   Nom de l'artiste      │
│                         │
│   ◄◄   ▶︎/❚❚   ►►      │
│   [═══════○────]        │
└─────────────────────────┘
```

---

## ✅ 4. Protection CSRF (Cross-Site Request Forgery)

**Problème initial :** Aucune protection contre les requêtes malveillantes cross-site.

**Solution implémentée :**

### A. Nouvelle classe CSRF
- Génération de tokens aléatoires (64 caractères hex)
- Validation avec `hash_equals()` (timing-attack safe)
- Stockage en session
- Helpers pratiques : `csrf_token()`, `csrf_field()`, `csrf_meta()`, `csrf_verify()`

### B. Intégration Frontend
- Meta tag automatique dans `<head>` : `<meta name="csrf-token" content="...">`
- Interception de tous les fetch() POST/PUT/DELETE
- Ajout automatique du header `X-CSRF-Token`

### C. Vérification Backend
Protection ajoutée sur toutes les actions sensibles :
- ✅ `SearchController::download()` - téléchargement YouTube
- ✅ `PlaylistController::create/update/delete()` - gestion playlists
- ✅ `PlaylistController::addSong/removeSong()` - modification contenu
- ✅ `MusicController::scan/delete()` - modification bibliothèque

**Fichiers créés/modifiés :**
- `src/Core/CSRF.php` - nouvelle classe
- `src/Core/helpers.php` - helpers csrf_*()
- `src/Views/layouts/main.php` - meta tag
- `public/assets/js/app.js` - interception fetch()
- Tous les contrôleurs - vérification csrf_verify()

**Utilisation :**

```php
// Dans un contrôleur
public function create(): void
{
    csrf_verify(); // Lance une erreur 403 si token invalide
    
    // ... reste du code
}
```

```javascript
// JavaScript - automatique pour tous les fetch POST
fetch('/api/playlists', {
    method: 'POST',
    body: JSON.stringify(data)
});
// Le header X-CSRF-Token est ajouté automatiquement
```

---

## 📊 Résumé des Changements

| Catégorie | Fichiers Modifiés | Impact |
|-----------|------------------|---------|
| **Métadonnées** | 2 fichiers | ⭐⭐⭐ Critique - UX |
| **Covers** | 4 fichiers | ⭐⭐⭐ Critique - UX |
| **MediaSession** | 1 fichier | ⭐⭐ Important - Mobile |
| **CSRF** | 9 fichiers | ⭐⭐⭐ Critique - Sécurité |

**Total : 16 fichiers modifiés ou créés**

---

## 🔮 Points Restants pour V3 (Non Critiques)

### 1. Système de Queue pour Téléchargements
**Problème :** Les longs téléchargements peuvent timeout.

**Solution future :**
- Table BDD `download_queue` (id, video_id, status, progress)
- Worker PHP en arrière-plan (cron ou supervisor)
- API polling pour progression temps réel
- UI avec barre de progression

**Estimation :** 4-6 heures de développement

---

### 2. Optimisations Performances

**Idées futures :**
- Cache getID3 (éviter re-scan à chaque lecture)
- Lazy loading des pochettes (IntersectionObserver)
- Compression WebP pour covers
- Index BDD sur colonnes de recherche

---

## 📝 Notes de Migration

Si vous avez déjà une base de données avec des chansons :

1. **Re-scanner la bibliothèque** pour extraire les vraies métadonnées :
   ```bash
   # Option 1 : Via l'interface (bouton "Scan Library")
   # Option 2 : Supprimer toutes les entrées et re-scanner
   ```

2. **Extraire les covers** des MP3 existants :
   ```php
   // Script PHP à exécuter une fois
   $music = new \App\Models\Music();
   $songs = $music->getAll();
   
   foreach ($songs as $song) {
       if (!$song->cover_path && file_exists($song->file_path)) {
           $coverPath = $music->extractCoverArt($song->file_path);
           if ($coverPath) {
               $music->update($song->id, ['cover_path' => $coverPath]);
           }
       }
   }
   ```

---

## ✨ Tester les Nouvelles Fonctionnalités

### Test Métadonnées
1. Scanner un dossier avec des MP3 correctement taggués
2. Vérifier que titre/artiste/album/durée sont corrects

### Test Covers
1. Télécharger une chanson YouTube → vérifier qu'elle a une pochette
2. Scanner un MP3 avec cover embarquée → vérifier extraction

### Test MediaSession
1. Ouvrir l'app sur mobile
2. Lancer une chanson
3. Verrouiller l'écran
4. Vérifier les contrôles sur lockscreen

### Test CSRF
1. Ouvrir DevTools → Network
2. Faire une action POST (créer playlist)
3. Vérifier header `X-CSRF-Token` dans la requête
4. Essayer de rejouer la requête sans token → doit échouer 403

---

**Date de mise à jour :** 25 Janvier 2026
**Version :** 2.0
