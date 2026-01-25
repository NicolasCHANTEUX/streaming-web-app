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
        csrf_verify();
        
        $name = input('name', '');
        $description = input('description', '');

        if (empty($name)) {
            json(['success' => false, 'error' => 'Name is required'], 400);
            return;
        }

        $playlistId = $this->playlistModel->create($name, $description);

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
