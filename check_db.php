<?php
require 'vendor/autoload.php';

// Charger le .env
try {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    error_log("DOTENV ERROR: " . $e->getMessage());
}

require 'config.php';

use App\Core\Database;

$db = Database::connect();

echo "=== Vérification de la base de données ===\n\n";

// Vérifier la table liked_songs
$tables = $db->query("SHOW TABLES LIKE 'liked_songs'")->fetchAll();

if (count($tables) > 0) {
    echo "✓ Table 'liked_songs' existe\n\n";
    
    // Afficher la structure
    echo "Structure de la table :\n";
    $structure = $db->query("DESCRIBE liked_songs")->fetchAll();
    foreach ($structure as $col) {
        echo "  - {$col->Field} ({$col->Type})\n";
    }
} else {
    echo "✗ Table 'liked_songs' MANQUANTE\n";
    echo "\nCréation de la table...\n";
    
    $sql = "
        CREATE TABLE IF NOT EXISTS liked_songs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            song_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (song_id),
            FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $db->exec($sql);
    echo "✓ Table 'liked_songs' créée avec succès\n";
}
