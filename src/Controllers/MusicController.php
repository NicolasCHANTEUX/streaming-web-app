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

    /**
     * Get song information as JSON
     */
    public function getSongInfo(string $id): void
    {
        $song = $this->musicModel->getById((int)$id);

        if (!$song) {
            json(['success' => false, 'error' => 'Song not found'], 404);
            return;
        }

        // Get file size if file exists
        $fileSize = file_exists($song->file_path) ? filesize($song->file_path) : 0;

        json([
            'success' => true,
            'song' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'album' => $song->album,
                'duration' => $song->duration,
                'cover_path' => $song->cover_path,
                'file_size' => $fileSize,
                'created_at' => $song->created_at
            ]
        ]);
    }

    /**
     * Update song information
     */
    public function updateSongInfo(string $id): void
    {
        csrf_verify();

        $song = $this->musicModel->getById((int)$id);

        if (!$song) {
            json(['success' => false, 'error' => 'Song not found'], 404);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $updateData = [];
        if (isset($input['title'])) {
            $updateData['title'] = trim($input['title']);
        }
        if (isset($input['artist'])) {
            $updateData['artist'] = trim($input['artist']);
        }
        if (isset($input['album'])) {
            $updateData['album'] = trim($input['album']);
        }

        if (empty($updateData)) {
            json(['success' => false, 'error' => 'No data to update'], 400);
            return;
        }

        $result = $this->musicModel->update((int)$id, $updateData);

        if ($result) {
            json(['success' => true, 'message' => 'Song updated successfully']);
        } else {
            json(['success' => false, 'error' => 'Failed to update song'], 500);
        }
    }

    /**
     * Delete song
     */
    public function deleteSong(string $id): void
    {
        csrf_verify();

        $song = $this->musicModel->getById((int)$id);

        if (!$song) {
            json(['success' => false, 'error' => 'Song not found'], 404);
            return;
        }

        // Delete file from disk
        if (file_exists($song->file_path)) {
            unlink($song->file_path);
        }

        // Delete cover if exists
        if ($song->cover_path) {
            $coverFullPath = config('paths.root') . '/public' . $song->cover_path;
            if (file_exists($coverFullPath)) {
                unlink($coverFullPath);
            }
        }

        // Delete from database
        $result = $this->musicModel->delete((int)$id);

        if ($result) {
            json(['success' => true, 'message' => 'Song deleted successfully']);
        } else {
            json(['success' => false, 'error' => 'Failed to delete song'], 500);
        }
    }
}
