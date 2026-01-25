<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Music
{
    public function getAll(): array
    {
        $stmt = Database::query("SELECT * FROM songs ORDER BY title ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?object
    {
        $stmt = Database::query("SELECT * FROM songs WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function search(string $query): array
    {
        $searchTerm = "%{$query}%";
        $stmt = Database::query(
            "SELECT * FROM songs 
             WHERE title LIKE ? OR artist LIKE ? OR album LIKE ?
             ORDER BY title ASC",
            [$searchTerm, $searchTerm, $searchTerm]
        );
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = Database::query(
            "INSERT INTO songs (title, artist, album, file_path, cover_path, duration, youtube_id, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $data['title'] ?? '',
                $data['artist'] ?? 'Unknown Artist',
                $data['album'] ?? 'Unknown Album',
                $data['file_path'],
                $data['cover_path'] ?? null,
                $data['duration'] ?? 0,
                $data['youtube_id'] ?? null
            ]
        );

        return (int) Database::connect()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE songs SET " . implode(', ', $fields) . " WHERE id = ?";
        Database::query($sql, $values);

        return true;
    }

    public function delete(int $id): bool
    {
        Database::query("DELETE FROM songs WHERE id = ?", [$id]);
        return true;
    }

    public function getRecent(int $limit = 10): array
    {
        $stmt = Database::query(
            "SELECT * FROM songs ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
        return $stmt->fetchAll();
    }

    public function scanMusicDirectory(): array
    {
        $musicPath = config('paths.music');
        $foundFiles = [];

        if (!is_dir($musicPath)) {
            return $foundFiles;
        }

        $extensions = ['mp3', 'm4a', 'aac', 'ogg', 'wav'];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($musicPath)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $extensions)) {
                    $foundFiles[] = [
                        'path' => $file->getPathname(),
                        'filename' => $file->getFilename(),
                        'size' => $file->getSize()
                    ];
                }
            }
        }

        return $foundFiles;
    }

    public function extractMetadata(string $filePath): array
    {
        // Cette fonction pourrait utiliser getID3 library pour extraire les métadonnées
        // Pour l'instant, on retourne des données basiques
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        
        return [
            'title' => $filename,
            'artist' => 'Unknown Artist',
            'album' => 'Unknown Album',
            'duration' => 0
        ];
    }
}
