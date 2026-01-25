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
        // Utilisation de getID3 pour extraire les vraies métadonnées
        $getID3 = new \getID3();
        
        // Configuration pour améliorer les performances
        $getID3->option_tag_id3v1 = true;
        $getID3->option_tag_id3v2 = true;
        $getID3->option_tag_lyrics3 = false;
        $getID3->option_tag_apetag = false;
        $getID3->encoding = 'UTF-8';
        
        try {
            $fileInfo = $getID3->analyze($filePath);
            \getid3_lib::CopyTagsToComments($fileInfo);
            
            // Extraction des tags avec fallback sur le nom de fichier
            $filename = pathinfo($filePath, PATHINFO_FILENAME);
            
            $title = $fileInfo['comments_html']['title'][0] ?? 
                     $fileInfo['tags']['id3v2']['title'][0] ?? 
                     $fileInfo['tags']['id3v1']['title'][0] ?? 
                     $filename;
                     
            $artist = $fileInfo['comments_html']['artist'][0] ?? 
                      $fileInfo['tags']['id3v2']['artist'][0] ?? 
                      $fileInfo['tags']['id3v1']['artist'][0] ?? 
                      'Unknown Artist';
                      
            $album = $fileInfo['comments_html']['album'][0] ?? 
                     $fileInfo['tags']['id3v2']['album'][0] ?? 
                     $fileInfo['tags']['id3v1']['album'][0] ?? 
                     'Unknown Album';
            
            $duration = isset($fileInfo['playtime_seconds']) 
                        ? (int)round($fileInfo['playtime_seconds']) 
                        : 0;
            
            // Nettoyage des balises HTML qui peuvent être dans les tags
            $title = strip_tags($title);
            $artist = strip_tags($artist);
            $album = strip_tags($album);
            
            return [
                'title' => $title,
                'artist' => $artist,
                'album' => $album,
                'duration' => $duration,
                'bitrate' => $fileInfo['audio']['bitrate'] ?? null,
                'sample_rate' => $fileInfo['audio']['sample_rate'] ?? null,
                'format' => $fileInfo['fileformat'] ?? 'unknown'
            ];
            
        } catch (\Exception $e) {
            // En cas d'erreur, retourner des valeurs par défaut
            error_log("getID3 error for {$filePath}: " . $e->getMessage());
            
            $filename = pathinfo($filePath, PATHINFO_FILENAME);
            return [
                'title' => $filename,
                'artist' => 'Unknown Artist',
                'album' => 'Unknown Album',
                'duration' => 0
            ];
        }
    }

    /**
     * Extract cover art from MP3 file and save it
     * Returns the path to the saved cover or null if not found
     */
    public function extractCoverArt(string $filePath): ?string
    {
        $getID3 = new \getID3();
        
        try {
            $fileInfo = $getID3->analyze($filePath);
            
            // Chercher l'image dans les tags ID3v2
            if (isset($fileInfo['id3v2']['APIC'])) {
                $apicData = $fileInfo['id3v2']['APIC'][0];
                
                if (isset($apicData['data'])) {
                    $imageData = $apicData['data'];
                    $mimeType = $apicData['mime'] ?? 'image/jpeg';
                    
                    // Déterminer l'extension selon le type MIME
                    $extension = match($mimeType) {
                        'image/png' => 'png',
                        'image/jpeg', 'image/jpg' => 'jpg',
                        default => 'jpg'
                    };
                    
                    // Générer un nom de fichier unique basé sur le hash du fichier
                    $coverFilename = md5($filePath) . '.' . $extension;
                    $coverPath = config('paths.root') . '/public/assets/images/covers/' . $coverFilename;
                    
                    // Sauvegarder l'image
                    if (file_put_contents($coverPath, $imageData)) {
                        return '/assets/images/covers/' . $coverFilename;
                    }
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Cover extraction error for {$filePath}: " . $e->getMessage());
            return null;
        }
    }
}
