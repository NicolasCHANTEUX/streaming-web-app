<?php
require 'vendor/autoload.php';
require 'config.php';

use App\Models\Music;

$music = new Music();
$songs = $music->getAll();

echo "=== FIXING FILE PATHS ===\n\n";

foreach ($songs as $song) {
    $oldPath = $song->file_path;
    $newPath = str_replace('/', DIRECTORY_SEPARATOR, $oldPath);
    
    if ($oldPath !== $newPath) {
        echo "ID: {$song->id}\n";
        echo "OLD: {$oldPath}\n";
        echo "NEW: {$newPath}\n";
        echo "File exists with new path: " . (file_exists($newPath) ? "YES ✓" : "NO ✗") . "\n";
        
        if (file_exists($newPath)) {
            $music->update($song->id, ['file_path' => $newPath]);
            echo "✓ UPDATED\n";
        } else {
            echo "✗ SKIPPED (file not found)\n";
        }
        echo "---\n\n";
    }
}

// Fix cover paths too
$songs = $music->getAll();
foreach ($songs as $song) {
    if ($song->cover_path) {
        $oldPath = $song->cover_path;
        $newPath = str_replace('/', DIRECTORY_SEPARATOR, $oldPath);
        
        if ($oldPath !== $newPath && file_exists($newPath)) {
            echo "Fixing cover for ID {$song->id}: {$newPath}\n";
            $music->update($song->id, ['cover_path' => $newPath]);
        }
    }
}

echo "\n=== VERIFICATION ===\n\n";

$songs = $music->getAll();
foreach ($songs as $song) {
    echo "ID {$song->id}: " . (file_exists($song->file_path) ? "✓ OK" : "✗ BROKEN") . "\n";
}
