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
        error_log('=== GET ALL PLAYLISTS API ===');
        $playlistModel = new \App\Models\Playlist();
        $playlists = $playlistModel->getAll();
        
        error_log('Playlists count: ' . count($playlists));
        error_log('Playlists type: ' . gettype($playlists));
        error_log('Playlists data: ' . json_encode($playlists));

        json(['success' => true, 'playlists' => $playlists]);
    }
}
