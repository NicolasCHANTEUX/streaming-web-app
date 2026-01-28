<?php

return [
    // Database configuration
    'database' => [
        'host'     => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
        'name'     => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'music_streaming',
        'user'     => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
        'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
    ],

    // Paths
    'paths' => [
        'root'   => __DIR__,
        // Si MUSIC_PATH est défini dans le .env (ex: /var/www/music), on l'utilise.
        // Sinon, on garde le dossier local storage/music
        'music'  => getenv('MUSIC_PATH') ?: __DIR__ . '/storage/music',
        
        // Idem pour les covers
        'covers' => __DIR__ . '/public/assets/images/covers',
    ],

    // YouTube Downloader (yt-dlp)
    'ytdlp' => [
        // Chemin vers yt-dlp (utiliser la commande globale si installée via WinGet/pip)
        'path' => getenv('YTDLP_PATH') ?: 'yt-dlp',
        
        // ffmpeg path (nécessaire pour conversion MP3)
        'ffmpeg_path' => getenv('FFMPEG_PATH') ?: 'ffmpeg',
    ],

    // Application settings
    'app' => [
        'name' => 'MyMusic Streaming',
        'url'  => getenv('APP_URL') ?: 'http://localhost',
        'env'  => getenv('APP_ENV') ?: 'development',
    ],
];