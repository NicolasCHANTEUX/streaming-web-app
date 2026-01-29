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

    public function getByFilePath(string $filePath): ?object
    {
        $stmt = Database::query("SELECT * FROM songs WHERE file_path = ?", [$filePath]);
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

    /**
     * Search for a song by exact title and artist (case insensitive)
     * Used for duplicate detection during import
     */
    public function searchByTitleAndArtist(string $title, string $artist): ?object
    {
        $stmt = Database::query(
            "SELECT * FROM songs 
             WHERE LOWER(title) = LOWER(?) AND LOWER(artist) = LOWER(?)
             LIMIT 1",
            [$title, $artist]
        );
        $result = $stmt->fetch();
        return $result ?: null;
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
                    
                    // Créer une image depuis les données
                    $image = imagecreatefromstring($imageData);
                    if ($image === false) {
                        error_log("Failed to create image from APIC data");
                        return null;
                    }
                    
                    // Optimiser et redimensionner l'image
                    $optimizedImage = $this->optimizeImage($image);
                    imagedestroy($image);
                    
                    if ($optimizedImage === null) {
                        return null;
                    }
                    
                    // Générer un nom de fichier unique en WebP
                    $coverFilename = md5($filePath) . '.webp';
                    $coverPath = config('paths.root') . '/public/assets/images/covers/' . $coverFilename;
                    
                    // Sauvegarder en WebP avec qualité optimisée
                    if (imagewebp($optimizedImage, $coverPath, 85)) {
                        imagedestroy($optimizedImage);
                        return '/assets/images/covers/' . $coverFilename;
                    }
                    
                    imagedestroy($optimizedImage);
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Cover extraction error for {$filePath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Optimize and resize an image to a maximum size
     * @param resource $image GD image resource
     * @param int $maxSize Maximum width/height in pixels
     * @return resource|null Optimized image or null on failure
     */
    private function optimizeImage($image, int $maxSize = 300)
    {
        try {
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Calculer les nouvelles dimensions en conservant le ratio
            if ($width > $maxSize || $height > $maxSize) {
                if ($width > $height) {
                    $newWidth = $maxSize;
                    $newHeight = (int)($height * ($maxSize / $width));
                } else {
                    $newHeight = $maxSize;
                    $newWidth = (int)($width * ($maxSize / $height));
                }
            } else {
                // L'image est déjà assez petite
                $newWidth = $width;
                $newHeight = $height;
            }
            
            // Créer une nouvelle image redimensionnée
            $optimized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Préserver la transparence pour PNG
            imagealphablending($optimized, false);
            imagesavealpha($optimized, true);
            
            // Redimensionner avec interpolation de haute qualité
            imagecopyresampled(
                $optimized, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $width, $height
            );
            
            return $optimized;
            
        } catch (\Exception $e) {
            error_log("Image optimization error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean a title by removing HTML entities and unwanted keywords in brackets/parentheses
     * @param string $title The title to clean
     * @return string The cleaned title
     */
    public static function cleanTitle(string $title): string
    {
        // First, decode HTML entities (&#201; -> É, &amp; -> &, etc.)
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Keywords to remove from parentheses and brackets
        $keywords = [
            'officiel', 'official', 'clip', 'video', 'audio', 
            'lyric', 'lyrics', 'visualizer', 'visualiser', 'vevo',
            'hd', '4k', 'music video', 'official video', 'official audio',
            'official music video', 'clip officiel', 'vidéo officielle',
            'official lyric video', 'lyric video', 'audio only'
        ];
        
        // Remove parentheses containing any of the keywords (case insensitive)
        foreach ($keywords as $keyword) {
            // Match parentheses with the keyword anywhere inside
            $title = preg_replace('/\s*\([^)]*' . preg_quote($keyword, '/') . '[^)]*\)/ui', '', $title);
        }
        
        // Remove brackets containing any of the keywords (case insensitive)
        foreach ($keywords as $keyword) {
            // Match brackets with the keyword anywhere inside
            $title = preg_replace('/\s*\[[^\]]*' . preg_quote($keyword, '/') . '[^\]]*\]/ui', '', $title);
        }
        
        // Remove keywords even when NOT in parentheses/brackets (often at the end of titles)
        // Sort keywords by length (longest first) to match "official music video" before "official video"
        $sortedKeywords = $keywords;
        usort($sortedKeywords, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($sortedKeywords as $keyword) {
            // Remove keyword with optional dash/pipe separator before it
            $title = preg_replace('/\s*[-–—|]\s*' . preg_quote($keyword, '/') . '\s*$/ui', '', $title);
            // Remove keyword at the end without separator
            $title = preg_replace('/\s+' . preg_quote($keyword, '/') . '\s*$/ui', '', $title);
        }
        
        // Clean up multiple spaces
        $title = preg_replace('/\s+/', ' ', $title);
        
        // Remove trailing dashes that might be left over
        $title = preg_replace('/\s*[-–—|]\s*$/', '', $title);
        
        return trim($title);
    }
}
