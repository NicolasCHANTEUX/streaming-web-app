<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
require '../config.php';

use App\Models\Music;

$music = new Music();
$song = $music->getById(3);

if (!$song) {
    die("Song not found");
}

echo "Song ID: {$song->id}\n";
echo "Title: {$song->title}\n";
echo "File Path: {$song->file_path}\n";
echo "File Exists: " . (file_exists($song->file_path) ? 'YES' : 'NO') . "\n";

if (file_exists($song->file_path)) {
    echo "File Size: " . filesize($song->file_path) . " bytes\n";
    echo "Mime Type: " . mime_content_type($song->file_path) . "\n";
    echo "\nAttempting to open file...\n";
    
    $fp = fopen($song->file_path, 'rb');
    if ($fp) {
        echo "✓ File opened successfully\n";
        echo "Reading first 100 bytes...\n";
        $data = fread($fp, 100);
        echo "✓ Read " . strlen($data) . " bytes\n";
        fclose($fp);
    } else {
        echo "✗ Failed to open file\n";
    }
}
