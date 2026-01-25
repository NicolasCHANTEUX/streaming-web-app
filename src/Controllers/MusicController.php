<?php

namespace App\Controllers;

use App\Models\Music;

class MusicController
{
    private Music $musicModel;

    public function __construct()
    {
        $this->musicModel = new Music();
    }

    public function index(): void
    {
        $songs = $this->musicModel->getAll();

        view('layouts/main', [
            'title' => 'All Music',
            'content' => 'pages/music',
            'data' => ['songs' => $songs]
        ]);
    }

    public function show(string $id): void
    {
        $song = $this->musicModel->getById((int)$id);

        if (!$song) {
            redirect('/');
            return;
        }

        view('layouts/main', [
            'title' => $song->title,
            'content' => 'pages/music-detail',
            'data' => ['song' => $song]
        ]);
    }

    public function stream(string $id): void
    {
        try {
            $song = $this->musicModel->getById((int)$id);

            if (!$song) {
                error_log("Stream error: Song ID {$id} not found in database");
                http_response_code(404);
                echo "Song not found";
                return;
            }

            error_log("Stream: Song ID {$id}, file_path: {$song->file_path}");
            error_log("Stream: file_exists: " . (file_exists($song->file_path) ? 'YES' : 'NO'));

            if (!file_exists($song->file_path)) {
                error_log("Stream error: File does not exist: {$song->file_path}");
                http_response_code(404);
                echo "File not found on disk";
                return;
            }

            $filePath = $song->file_path;
            $fileSize = filesize($filePath);
            
            // Fallback for Windows where mime_content_type might not be available
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($filePath);
            } else {
                // Default to audio/mpeg for MP3 files
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mime = match($extension) {
                    'mp3' => 'audio/mpeg',
                    'mp4', 'm4a' => 'audio/mp4',
                    'ogg' => 'audio/ogg',
                    'wav' => 'audio/wav',
                    'flac' => 'audio/flac',
                    default => 'audio/mpeg'
                };
            }

            error_log("Stream: Starting stream - Size: {$fileSize}, Mime: {$mime}");

            // Support for range requests (seeking in audio)
            $start = 0;
            $end = $fileSize - 1;

            if (isset($_SERVER['HTTP_RANGE'])) {
                $range = $_SERVER['HTTP_RANGE'];
                $range = str_replace('bytes=', '', $range);
                list($start, $end) = explode('-', $range);
                
                $start = intval($start);
                $end = $end ? intval($end) : $fileSize - 1;

                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
            }

            header("Content-Type: {$mime}");
            header("Accept-Ranges: bytes");
            header("Content-Length: " . ($end - $start + 1));

            $fp = fopen($filePath, 'rb');
            fseek($fp, $start);
            
            $buffer = 8192;
            $bytesRemaining = $end - $start + 1;

            while ($bytesRemaining > 0 && !feof($fp)) {
                $bytesToRead = min($buffer, $bytesRemaining);
                echo fread($fp, $bytesToRead);
                flush();
                $bytesRemaining -= $bytesToRead;
            }

            fclose($fp);
            exit;
            
        } catch (\Exception $e) {
            error_log("Stream exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo "Error streaming file";
        }
    }

    public function delete(string $id): void
    {
        csrf_verify();
        
        $song = $this->musicModel->getById((int)$id);

        if ($song) {
            // Delete file if exists
            if (file_exists($song->file_path)) {
                unlink($song->file_path);
            }

            // Delete cover if exists
            if ($song->cover_path && file_exists($song->cover_path)) {
                unlink($song->cover_path);
            }

            // Delete from database
            $this->musicModel->delete((int)$id);
        }

        redirect('/music');
    }

    public function search(): void
    {
        $query = input('q', '');
        $songs = [];

        if ($query) {
            $songs = $this->musicModel->search($query);
        }

        view('layouts/main', [
            'title' => 'Search Results',
            'content' => 'pages/search-results',
            'data' => [
                'query' => $query,
                'songs' => $songs
            ]
        ]);
    }

    public function scan(): void
    {
        csrf_verify();
        
        // Autoriser le script à tourner sans limite de temps pour scanner de grandes bibliothèques
        set_time_limit(0);
        
        $files = $this->musicModel->scanMusicDirectory();
        $imported = 0;

        foreach ($files as $file) {
            $metadata = $this->musicModel->extractMetadata($file['path']);
            
            // Extraire la pochette d'album si présente
            $coverPath = $this->musicModel->extractCoverArt($file['path']);
            
            $this->musicModel->create([
                'title' => $metadata['title'],
                'artist' => $metadata['artist'],
                'album' => $metadata['album'],
                'file_path' => $file['path'],
                'duration' => $metadata['duration'],
                'cover_path' => $coverPath
            ]);

            $imported++;
        }

        json(['success' => true, 'imported' => $imported]);
    }
}
