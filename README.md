# Music Streaming Web App

Une application web de streaming musical personnelle permettant d'écouter votre collection de musique et de télécharger de nouveaux titres depuis YouTube. **Design moderne avec Tailwind CSS**.

> 📖 **[Voir les améliorations V2.0](IMPROVEMENTS-V2.md)** - Métadonnées réelles, covers, MediaSession API, protection CSRF

## Fonctionnalités

- 🎵 **Streaming Audio** : Écoutez votre collection de musique en MP3
- 🔍 **Recherche YouTube** : Trouvez et téléchargez de la musique depuis YouTube
- 📱 **Interface Mobile-First** : Optimisée pour les appareils mobiles (PWA) avec Tailwind CSS
- 📂 **Gestion de Playlists** : Créez et organisez vos playlists
- ⚡ **Lecteur Audio Moderne** : Contrôles play/pause, volume, seek
- 🎨 **Interface Dark Mode** : Design sombre et épuré inspiré de Spotify
- 🎭 **Métadonnées ID3** : Lecture automatique des tags (titre, artiste, album, durée) via getID3
- 🖼️ **Pochettes d'album** : Extraction automatique des covers depuis YouTube et fichiers MP3
- 📲 **Contrôles Mobile** : MediaSession API pour contrôler la musique écran verrouillé
- 🔒 **Protection CSRF** : Sécurité renforcée contre les attaques cross-site

## Prérequis

- PHP 8.0 ou supérieur
- MySQL/MariaDB
- Composer
- **Node.js & npm** (pour Tailwind CSS)
- [yt-dlp](https://github.com/yt-dlp/yt-dlp) installé et accessible
- [ffmpeg](https://ffmpeg.org/) pour la conversion audio

## Installation

### 1. Cloner le repository

```bash
git clone https://github.com/NicolasCHANTEUX/streaming-web-app.git
cd streaming-web-app
```

### 2. Installer les dépendances

**PHP (Composer) :**
```bash
composer install
```

**Node.js (Tailwind CSS) :**
```bash
npm install
npm run build
```

Pour le développement avec hot-reload :
```bash
npm run watch
```

### 3. Configuration de la base de données

Créez la base de données en important le fichier SQL :

```bash
mysql -u root -p < database.sql
```

### 4. Configuration de l'application

Le fichier `config.php` contient déjà la configuration par défaut. Modifiez-le selon vos besoins :

```php
'database' => [
    'host' => 'localhost',
    'name' => 'music_streaming',
    'user' => 'root',
    'password' => 'votre_mot_de_passe',
],
```

### 5. Installer yt-dlp

**Windows :**
```bash
winget install yt-dlp
# ou téléchargez depuis https://github.com/yt-dlp/yt-dlp/releases
```

**Linux/Mac :**
```bash
pip install yt-dlp
```

### 6. Installer ffmpeg

**Windows :**
```bash
winget install ffmpeg
```

**Linux :**
```bash
sudo apt install ffmpeg
```

### 7. Configuration du serveur web

**Option 1 : Serveur PHP intégré (développement)**
```bash
cd public
php -S localhost:8000
```

**Option 2 : Apache**

Configurez votre VirtualHost pour pointer vers le dossier `public/`.

### 8. Permissions

Assurez-vous que le dossier `storage/music` est accessible en écriture :

```bash
chmod -R 775 storage
```

## Structure du Projet

```
streaming-web-app/
├── public/              # Point d'entrée web
│   ├── index.php       # Router principal
│   └── assets/         # CSS, JS, Images
├── src/
│   ├── Controllers/    # Contrôleurs MVC
│   ├── Models/         # Modèles (logique métier)
│   ├── Views/          # Vues
│   │   ├── layouts/    # Templates principaux
│   │   ├── pages/      # Pages de l'app
│   │   └── components/ # Composants réutilisables
│   └── Core/           # Classes core (Router, Database)
├── storage/
│   └── music/          # Fichiers audio
├── config.php          # Configuration
├── database.sql        # Schéma de base de données
└── composer.json       # Dépendances PHP
```

## Architecture MVC

Le projet utilise une architecture MVC from scratch :

- **Models** : Gestion des données (Music, Playlist, YoutubeDownloader)
- **Views** : Interface utilisateur découpée en composants
- **Controllers** : Logique de routage et traitement des requêtes

### Composants

Les vues utilisent massivement les composants pour un code propre :

```php
// Dans une page
<?php component('music-card', ['track' => $song]); ?>
```

## Utilisation

### Ajouter de la musique

1. Allez sur la page "Search"
2. Recherchez un titre sur YouTube
3. Cliquez sur "Download" pour l'ajouter à votre bibliothèque

### Créer une playlist

1. Allez sur "Playlists"
2. Cliquez sur "Create Playlist"
3. Ajoutez des titres depuis votre bibliothèque

### Scanner un dossier existant

Si vous avez déjà des fichiers MP3, utilisez l'API scan :

```bash
curl -X POST http://localhost:8000/api/music/scan
```

## API Endpoints

### Musique
- `GET /music` - Liste toutes les musiques
- `GET /music/{id}` - Détails d'une musique
- `GET /stream/{id}` - Stream audio
- `POST /api/music/scan` - Scanner le dossier music

### YouTube
- `GET /api/youtube/search?q=query` - Rechercher sur YouTube
- `POST /api/youtube/download` - Télécharger une vidéo
- `GET /api/youtube/status` - Vérifier si yt-dlp est disponible

### Playlists
- `GET /playlists` - Liste des playlists
- `POST /api/playlists/create` - Créer une playlist
- `POST /api/playlists/{id}/add-song` - Ajouter un titre
- `POST /api/playlists/{id}/remove-song` - Retirer un titre

## Développement

### Ajouter une nouvelle fonctionnalité

1. Créer le Model dans `src/Models/`
2. Créer le Controller dans `src/Controllers/`
3. Créer les vues dans `src/Views/pages/`
4. Créer les composants dans `src/Views/components/`
5. Ajouter les routes dans `public/index.php`

## Sécurité

⚠️ **Important** : Cette application est conçue pour un usage personnel local. Avant de l'exposer sur Internet :

- Ajoutez une authentification
- Utilisez HTTPS
- Configurez un VPN (comme Tailscale)
- Mettez à jour régulièrement yt-dlp

## Légalité

Le téléchargement de contenus protégés par le droit d'auteur est illégal dans de nombreux pays. Cet outil doit être utilisé uniquement pour :

- Vos propres créations
- Du contenu libre de droits
- Du contenu que vous possédez déjà

## Contribuer

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## Licence

MIT License - Utilisez ce code comme bon vous semble !

## Auteur

Nicolas CHANTEUX

## Support

Pour toute question ou problème, ouvrez une issue sur GitHub.
