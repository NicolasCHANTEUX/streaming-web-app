<?php
require 'vendor/autoload.php';
require 'config.php';

use App\Models\Music;

$music = new Music();
$songs = $music->getAll();

echo "=== SONGS IN DATABASE ===\n\n";

foreach ($songs as $song) {
    echo "ID: {$song->id}\n";
    echo "Title: {$song->title}\n";
    echo "File Path: {$song->file_path}\n";
    echo "File Exists: " . (file_exists($song->file_path) ? "YES ✓" : "NO ✗") . "\n";
    
    if (!file_exists($song->file_path)) {
        // Essayer avec des variations du chemin
        $normalized = str_replace('/', DIRECTORY_SEPARATOR, $song->file_path);
        echo "Normalized Path: {$normalized}\n";
        echo "Normalized Exists: " . (file_exists($normalized) ? "YES ✓" : "NO ✗") . "\n";
    }
    
    echo "---\n\n";
}

echo "\n=== FILES IN STORAGE ===\n\n";

$files = glob(config('paths.music') . '/*.mp3');
foreach ($files as $file) {
    echo "File: {$file}\n";
    echo "Exists: " . (file_exists($file) ? "YES ✓" : "NO ✗") . "\n";
    echo "Size: " . filesize($file) . " bytes\n";
    echo "---\n\n";
}
