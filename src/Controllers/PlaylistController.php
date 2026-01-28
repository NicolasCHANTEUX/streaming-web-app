<?php

namespace App\Controllers;

use App\Models\Playlist;
use App\Models\Music;

class PlaylistController
{
    private Playlist $playlistModel;
    private Music $musicModel;

    public function __construct()
    {
        $this->playlistModel = new Playlist();
        $this->musicModel = new Music();
    }

    public function index(): void
    {
        $playlists = $this->playlistModel->getAll();

        view('layouts/main', [
            'title' => 'Playlists',
            'content' => 'pages/playlists',
            'data' => ['playlists' => $playlists]
        ]);
    }

    public function show(string $id): void
    {
        $playlist = $this->playlistModel->getById((int)$id);

        if (!$playlist) {
            redirect('/playlists');
            return;
        }

        $songs = $this->playlistModel->getSongs((int)$id);

        view('layouts/main', [
            'title' => $playlist->name,
            'content' => 'pages/playlist-detail',
            'data' => [
                'playlist' => $playlist,
                'songs' => $songs
            ]
        ]);
    }

    public function create(): void
    {
        error_log("=== CREATE PLAYLIST DEBUG ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
        error_log("POST data: " . print_r($_POST, true));
        error_log("Raw input: " . file_get_contents('php://input'));
        error_log("Session data: " . print_r($_SESSION ?? [], true));
        error_log("CSRF token from POST: " . ($_POST['csrf_token'] ?? 'not set'));
        error_log("CSRF token from header: " . ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'not set'));
        
        try {
            csrf_verify();
            error_log("CSRF verification passed");
        } catch (\Exception $e) {
            error_log("CSRF verification failed: " . $e->getMessage());
            throw $e;
        }
        
        $name = input('name', '');
        $description = input('description', '');
        
        error_log("Name from input: '$name'");
        error_log("Description from input: '$description'");

        if (empty($name)) {
            error_log("Validation failed: empty name");
            json(['success' => false, 'error' => 'Name is required'], 400);
            return;
        }

        error_log("Creating playlist with name: $name");
        $playlistId = $this->playlistModel->create($name, $description);
        error_log("Playlist created with ID: $playlistId");

        json(['success' => true, 'playlist_id' => $playlistId]);
    }

    public function update(string $id): void
    {
        csrf_verify();
        
        $data = [];

        if ($name = input('name')) {
            $data['name'] = $name;
        }

        if ($description = input('description')) {
            $data['description'] = $description;
        }

        if (empty($data)) {
            json(['success' => false, 'error' => 'No data to update'], 400);
            return;
        }

        $this->playlistModel->update((int)$id, $data);

        json(['success' => true]);
    }

    public function delete(string $id): void
    {
        csrf_verify();
        
        $this->playlistModel->delete((int)$id);
        redirect('/playlists');
    }

    public function addSong(string $id): void
    {
        csrf_verify();
        
        $songId = input('song_id', '');

        if (empty($songId)) {
            json(['success' => false, 'error' => 'Song ID is required'], 400);
            return;
        }

        $this->playlistModel->addSong((int)$id, (int)$songId);

        json(['success' => true]);
    }

    public function removeSong(string $id): void
    {
        csrf_verify();
        
        $songId = input('song_id', '');

        if (empty($songId)) {
            json(['success' => false, 'error' => 'Song ID is required'], 400);
            return;
        }

        $this->playlistModel->removeSong((int)$id, (int)$songId);

        json(['success' => true]);
    }
}
