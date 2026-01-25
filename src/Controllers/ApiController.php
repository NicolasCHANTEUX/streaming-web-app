<?php

namespace App\Controllers;

use App\Models\Music;

class ApiController
{
    private Music $musicModel;

    public function __construct()
    {
        $this->musicModel = new Music();
    }

    public function getSong(string $id): void
    {
        $song = $this->musicModel->getById((int)$id);

        if (!$song) {
            json(['success' => false, 'error' => 'Song not found'], 404);
            return;
        }

        json(['success' => true, 'song' => $song]);
    }

    public function getAllPlaylists(): void
    {
        $playlistModel = new \App\Models\Playlist();
        $playlists = $playlistModel->getAll();

        json(['success' => true, 'playlists' => $playlists]);
    }
}
