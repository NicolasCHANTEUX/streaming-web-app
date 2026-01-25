<?php

return [
    // Database configuration
    'database' => [
        'host'     => getenv('DB_HOST') ?: 'localhost',
        'name'     => getenv('DB_NAME') ?: 'music_streaming',
        'user'     => getenv('DB_USER') ?: 'music_user',
        'password' => getenv('DB_PASS') ?: '123',
    ],

    // Paths
    'paths' => [
        // Si MUSIC_PATH est défini dans le .env (ex: /var/www/music), on l'utilise.
        // Sinon, on garde le dossier local storage/music
        'music'  => getenv('MUSIC_PATH') ?: __DIR__ . '/storage/music',
        
        // Idem pour les covers
        'covers' => __DIR__ . '/public/assets/images/covers',
    ],

    // YouTube Downloader (yt-dlp)
    'ytdlp' => [
        // Sur le serveur, ce sera sûrement /usr/bin/yt-dlp
        // En local, juste 'yt-dlp' suffit souvent si c'est dans le PATH
        'path' => getenv('YTDLP_PATH') ?: 'yt-dlp',
    ],

    // Application settings
    'app' => [
        'name' => 'MyMusic Streaming',
        'url'  => getenv('APP_URL') ?: 'http://localhost',
        'env'  => getenv('APP_ENV') ?: 'development',
    ],
];