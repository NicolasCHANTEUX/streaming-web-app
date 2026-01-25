<?php

namespace App\Models;

class YoutubeDownloader
{
    private string $ytdlpPath;
    private string $musicPath;
    private string $ffmpegOption;

    public function __construct()
    {
        $ytdlpPath = config('ytdlp.path', 'yt-dlp');
        $ffmpegPath = config('ytdlp.ffmpeg_path', '');
        
        // Échapper le chemin pour Windows (chemins avec espaces)
        // Sur Windows, utiliser des guillemets doubles, sur Linux/Mac escapeshellarg suffit
        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows: entourer de guillemets doubles
            $this->ytdlpPath = '"' . $ytdlpPath . '"';
            $this->ffmpegOption = $ffmpegPath ? '--ffmpeg-location "' . $ffmpegPath . '"' : '';
        } else {
            // Linux/Mac: utiliser escapeshellarg
            $this->ytdlpPath = escapeshellarg($ytdlpPath);
            $this->ffmpegOption = $ffmpegPath ? '--ffmpeg-location ' . escapeshellarg($ffmpegPath) : '';
        }
        
        $this->musicPath = config('paths.music');
    }

    /**
     * Search YouTube and return results as JSON
     */
    public function search(string $query, int $maxResults = 10): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }

        // Add "Audio" to search for better music results
        $searchString = "ytsearch{$maxResults}:{$query} Audio";
        
        // Sur Windows, utiliser des guillemets doubles au lieu de escapeshellarg
        if (DIRECTORY_SEPARATOR === '\\') {
            $searchQuery = '"' . $searchString . '"';
        } else {
            $searchQuery = escapeshellarg($searchString);
        }

        $cmd = "{$this->ytdlpPath} {$searchQuery} --dump-json --flat-playlist --no-playlist 2>&1";

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            error_log("yt-dlp search error: " . implode("\n", $output));
            return [];
        }

        $results = [];
        foreach ($output as $line) {
            $data = json_decode($line, true);
            if ($data && isset($data['id'])) {
                $results[] = [
                    'id' => $data['id'],
                    'title' => $data['title'] ?? 'Unknown',
                    'duration' => $data['duration'] ?? 0,
                    'thumbnail' => $data['thumbnail'] ?? $data['thumbnails'][0]['url'] ?? '',
                    'channel' => $data['channel'] ?? $data['uploader'] ?? 'Unknown',
                    'url' => "https://www.youtube.com/watch?v={$data['id']}"
                ];
            }
        }

        return $results;
    }

    /**
     * Download audio from YouTube by video ID
     */
    public function download(string $videoId, ?string $customTitle = null): array
    {
        if (empty($videoId)) {
            return ['success' => false, 'error' => 'Video ID is required'];
        }

        // Vérifier l'espace disque disponible (minimum 100 MB)
        $freeSpace = disk_free_space($this->musicPath);
        $minSpace = 100 * 1024 * 1024; // 100 MB
        
        if ($freeSpace === false || $freeSpace < $minSpace) {
            $freeSpaceMB = $freeSpace !== false ? round($freeSpace / 1024 / 1024, 2) : 'unknown';
            error_log("Insufficient disk space: {$freeSpaceMB} MB available");
            return [
                'success' => false, 
                'error' => "Espace disque insuffisant ({$freeSpaceMB} MB disponibles, minimum 100 MB requis)"
            ];
        }

        $url = "https://www.youtube.com/watch?v=" . $videoId;
        
        // Output template
        $outputTemplate = $this->musicPath . '/%(title)s.%(ext)s';
        if ($customTitle) {
            $outputTemplate = $this->musicPath . '/' . $this->sanitizeFilename($customTitle) . '.%(ext)s';
        }

        // Sur Windows, utiliser des guillemets doubles au lieu de escapeshellarg
        if (DIRECTORY_SEPARATOR === '\\') {
            $outputArg = '"' . $outputTemplate . '"';
            $urlArg = '"' . $url . '"';
        } else {
            $outputArg = escapeshellarg($outputTemplate);
            $urlArg = escapeshellarg($url);
        }

        // Command to download audio only in MP3 format
        // --write-thumbnail sauvegarde aussi l'image séparément
        // --convert-thumbnails jpg convertit en JPG pour compatibilité web
        $cmd = "{$this->ytdlpPath} {$this->ffmpegOption} -x --audio-format mp3 --audio-quality 0 " .
               "--embed-thumbnail --add-metadata " .
               "--write-thumbnail --convert-thumbnails jpg " .
               "--output {$outputArg} {$urlArg} 2>&1";

        // Log de la commande pour debug
        error_log("Executing yt-dlp command: " . $cmd);

        exec($cmd, $output, $returnCode);

        // Log du résultat
        error_log("yt-dlp return code: {$returnCode}");
        error_log("yt-dlp output: " . implode("\n", $output));

        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            error_log("yt-dlp download error: " . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }

        // Find the downloaded file
        $downloadedFile = $this->findLatestFile($this->musicPath, 'mp3');

        if (!$downloadedFile) {
            return ['success' => false, 'error' => 'File not found after download'];
        }

        // Trouver et déplacer la thumbnail vers le dossier covers
        $coverPath = null;
        $baseFilename = pathinfo($downloadedFile, PATHINFO_FILENAME);
        $thumbnailFile = $this->musicPath . '/' . $baseFilename . '.jpg';
        
        if (file_exists($thumbnailFile)) {
            $coverFilename = md5($videoId) . '.jpg';
            $coverDestination = config('paths.root') . '/public/assets/images/covers/' . $coverFilename;
            
            if (rename($thumbnailFile, $coverDestination)) {
                $coverPath = '/assets/images/covers/' . $coverFilename;
            }
        }

        return [
            'success' => true,
            'file_path' => $downloadedFile,
            'youtube_id' => $videoId,
            'cover_path' => $coverPath
        ];
    }

    /**
     * Get video info without downloading
     */
    public function getVideoInfo(string $videoId): ?array
    {
        $urlString = "https://www.youtube.com/watch?v={$videoId}";
        
        // Sur Windows, utiliser des guillemets doubles au lieu de escapeshellarg
        if (DIRECTORY_SEPARATOR === '\\') {
            $url = '"' . $urlString . '"';
        } else {
            $url = escapeshellarg($urlString);
        }
        
        $cmd = "{$this->ytdlpPath} {$this->ffmpegOption} {$url} --dump-json --no-playlist 2>&1";

        error_log("getVideoInfo command: " . $cmd);

        exec($cmd, $output, $returnCode);

        error_log("getVideoInfo return code: {$returnCode}");
        error_log("getVideoInfo output: " . implode("\n", $output));

        if ($returnCode !== 0) {
            error_log("getVideoInfo failed for video {$videoId}");
            return null;
        }

        $json = implode('', $output);
        $data = json_decode($json, true);

        if (!$data) {
            return null;
        }

        return [
            'id' => $data['id'],
            'title' => $data['title'] ?? 'Unknown',
            'artist' => $data['artist'] ?? $data['uploader'] ?? 'Unknown',
            'duration' => $data['duration'] ?? 0,
            'thumbnail' => $data['thumbnail'] ?? ''
        ];
    }

    /**
     * Check if yt-dlp is installed and accessible
     */
    public function isAvailable(): bool
    {
        $cmd = "{$this->ytdlpPath} --version 2>&1";
        exec($cmd, $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Get yt-dlp version
     */
    public function getVersion(): ?string
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $cmd = "{$this->ytdlpPath} --version 2>&1";
        exec($cmd, $output);
        return $output[0] ?? null;
    }

    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters that might cause issues
        $filename = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        return trim($filename, '_');
    }

    private function findLatestFile(string $directory, string $extension): ?string
    {
        // Normaliser le chemin pour glob() (utilise toujours /)
        $directory = str_replace('\\', '/', $directory);
        
        $pattern = $directory . '/*.' . $extension;
        $files = glob($pattern);
        
        if (empty($files)) {
            return null;
        }

        // Sort by modification time, newest first
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Normaliser le résultat pour utiliser les séparateurs du système
        $latestFile = str_replace('/', DIRECTORY_SEPARATOR, $files[0]);
        
        return $latestFile;
    }
}
