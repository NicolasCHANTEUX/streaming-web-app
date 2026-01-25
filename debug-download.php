<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Core/helpers.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de téléchargement YouTube</h1>";

try {
    $downloader = new \App\Models\YoutubeDownloader();
    
    echo "<h2>Configuration</h2>";
    echo "<pre>";
    echo "Music Path: " . config('paths.music') . "\n";
    echo "yt-dlp Path: " . config('ytdlp.path') . "\n";
    echo "ffmpeg Path: " . config('ytdlp.ffmpeg_path') . "\n";
    echo "</pre>";
    
    echo "<h2>Test de recherche</h2>";
    $results = $downloader->search('test', 1);
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    if (!empty($results)) {
        $videoId = $results[0]['id'];
        echo "<h2>Test de téléchargement (Video ID: {$videoId})</h2>";
        
        $result = $downloader->download($videoId);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERREUR</h2>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
