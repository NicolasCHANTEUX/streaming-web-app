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
        $song = $this->musicModel->getById((int)$id);

        if (!$song || !file_exists($song->file_path)) {
            http_response_code(404);
            echo "File not found";
            return;
        }

        $filePath = $song->file_path;
        $fileSize = filesize($filePath);
        $mime = mime_content_type($filePath);

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
