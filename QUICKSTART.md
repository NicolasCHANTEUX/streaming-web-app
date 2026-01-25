# Guide de Démarrage Rapide

## Étapes d'installation

### 1. Installation des dépendances

**PHP (Composer) :**
```bash
composer install
```

**Node.js (Tailwind CSS) :**
```bash
npm install
```

Si vous n'avez pas npm/Node.js, téléchargez depuis https://nodejs.org/

### 2. Compilation de Tailwind CSS

```bash
npm run build
```

Pour le développement avec auto-reload :
```bash
npm run watch
```

### 3. Configuration de la base de données

1. Créez la base de données MySQL :
   ```bash
   mysql -u root -p
   ```

2. Importez le schéma :
   ```sql
   source database.sql
   ```
   
   Ou directement :
   ```bash
   mysql -u root -p < database.sql
   ```

3. Modifiez `config.php` si nécessaire pour ajuster les identifiants de connexion :
   ```php
   'database' => [
       'host' => 'localhost',
       'name' => 'music_streaming',
       'user' => 'root',
       'password' => 'VOTRE_MOT_DE_PASSE',
   ],
   ```

### 3. Installation de yt-dlp et ffmpeg

**Windows (avec winget) :**
```powershell
winget install yt-dlp
winget install ffmpeg
```

**Windows (manuel) :**
- Téléchargez yt-dlp : https://github.com/yt-dlp/yt-dlp/releases
- Téléchargez ffmpeg : https://ffmpeg.org/download.html
- Ajoutez-les au PATH ou modifiez `config.php` pour spécifier le chemin complet

**Linux/Mac :**
```bash
# yt-dlp
pip install yt-dlp

# ffmpeg
sudo apt install ffmpeg  # Debian/Ubuntu
brew install ffmpeg      # macOS
```

### 4. Vérification de l'installation de yt-dlp

```bash
yt-dlp --version
```

Vous devriez voir une version s'afficher.

### 5. Lancement du serveur de développement

```bash
cd public
php -S localhost:8000
```

### 6. Accéder à l'application

Ouvrez votre navigateur et allez sur : http://localhost:8000

## Configuration pour un serveur distant

Si vous voulez déployer sur votre vieux PC serveur :

### Option 1 : Apache

1. Configurez un VirtualHost pointant vers le dossier `public/`
2. Activez mod_rewrite
3. Redémarrez Apache

### Option 2 : Nginx

Configuration Nginx exemple :

```nginx
server {
    listen 80;
    server_name music.local;
    root /path/to/streaming-web-app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Premiers pas

### 1. Ajouter de la musique via YouTube

1. Allez sur "Search" dans la navigation
2. Recherchez un titre (ex: "Daft Punk Get Lucky")
3. Cliquez sur "Download" à côté du résultat souhaité
4. Attendez que le téléchargement se termine
5. La musique apparaît maintenant dans votre bibliothèque !

### 2. Scanner un dossier existant

Si vous avez déjà des MP3 :

1. Placez-les dans `storage/music/`
2. Allez sur la page "Music Library"
3. Cliquez sur "Scan Directory"
4. Tous les fichiers seront importés dans la base de données

### 3. Créer une playlist

1. Allez sur "Playlists"
2. Cliquez sur "Create Playlist"
3. Donnez un nom et une description
4. Ajoutez des titres depuis votre bibliothèque

## Dépannage

### Problème : "Database connection failed"

- Vérifiez que MySQL est démarré
- Vérifiez les identifiants dans `config.php`
- Assurez-vous que la base `music_streaming` existe

### Problème : "yt-dlp not found"

- Vérifiez l'installation : `yt-dlp --version`
- Si installé ailleurs, modifiez le chemin dans `config.php` :
  ```php
  'ytdlp' => [
      'path' => 'C:/tools/yt-dlp.exe',  // Chemin complet
  ],
  ```

### Problème : Le téléchargement échoue

- Mettez à jour yt-dlp : `pip install --upgrade yt-dlp`
- Vérifiez que ffmpeg est installé : `ffmpeg -version`
- Vérifiez les permissions du dossier `storage/music/`

### Problème : "Class 'App\...' not found"

- Exécutez : `composer dump-autoload`

## Accès depuis le téléphone (réseau local)

1. Trouvez l'IP de votre serveur :
   ```bash
   # Windows
   ipconfig
   
   # Linux/Mac
   ip addr
   ```

2. Lancez le serveur avec cette IP :
   ```bash
   php -S 0.0.0.0:8000
   ```

3. Sur votre téléphone, accédez à : `http://IP_DU_SERVEUR:8000`

## Accès depuis l'extérieur (Internet)

⚠️ **ATTENTION : Ne pas exposer directement sur Internet sans sécurité !**

**Solution recommandée : VPN (Tailscale)**

1. Installez Tailscale sur le serveur : https://tailscale.com/
2. Installez Tailscale sur votre téléphone
3. Accédez à votre serveur via l'IP Tailscale

## Commandes utiles

```bash
# Mettre à jour yt-dlp
pip install --upgrade yt-dlp

# Vérifier l'espace disque
df -h storage/music

# Voir les logs PHP (si lancé avec php -S)
# Les erreurs s'affichent dans le terminal

# Nettoyer le cache Composer
composer clear-cache

# Régénérer l'autoload
composer dump-autoload
```

## Prochaines étapes

- [ ] Ajouter une authentification
- [ ] Implémenter l'upload de fichiers MP3
- [ ] Ajouter des statistiques d'écoute
- [ ] Améliorer l'extraction des métadonnées (getID3)
- [ ] Ajouter la gestion des pochettes d'album
- [ ] Implémenter un mode shuffle et repeat

Bon streaming ! 🎵
