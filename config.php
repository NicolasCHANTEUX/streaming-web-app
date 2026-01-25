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
        'root'   => __DIR__,
        // Si MUSIC_PATH est défini dans le .env (ex: /var/www/music), on l'utilise.
        // Sinon, on garde le dossier local storage/music
        'music'  => getenv('MUSIC_PATH') ?: __DIR__ . '/storage/music',
        
        // Idem pour les covers
        'covers' => __DIR__ . '/public/assets/images/covers',
    ],

    // YouTube Downloader (yt-dlp)
    'ytdlp' => [
        // Windows: Chemin complet vers yt-dlp.exe installé via WinGet
        // Linux/Mac: /usr/bin/yt-dlp ou /usr/local/bin/yt-dlp
        'path' => getenv('YTDLP_PATH') ?: (DIRECTORY_SEPARATOR === '\\'
            ? getenv('LOCALAPPDATA') . '\\Microsoft\\WinGet\\Packages\\yt-dlp.yt-dlp_Microsoft.Winget.Source_8wekyb3d8bbwe\\yt-dlp.exe'
            : 'yt-dlp'),
        
        // ffmpeg path (nécessaire pour conversion MP3)
        'ffmpeg_path' => getenv('FFMPEG_PATH') ?: (DIRECTORY_SEPARATOR === '\\'
            ? getenv('LOCALAPPDATA') . '\\Microsoft\\WinGet\\Packages\\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\\ffmpeg-8.0.1-full_build\\bin\\ffmpeg.exe'
            : 'ffmpeg'),
    ],

    // Application settings
    'app' => [
        'name' => 'MyMusic Streaming',
        'url'  => getenv('APP_URL') ?: 'http://localhost',
        'env'  => getenv('APP_ENV') ?: 'development',
    ],
];