<?php

namespace App\Controllers;

use App\Models\Music;
use App\Models\Playlist;

class HomeController
{
    public function index(): void
    {
        $musicModel = new Music();
        $playlistModel = new Playlist();

        $recentSongs = $musicModel->getRecent(20);
        $playlists = $playlistModel->getAll();

        view('layouts/main', [
            'title' => 'Home - Music Streaming',
            'content' => 'pages/home',
            'data' => [
                'recentSongs' => $recentSongs,
                'playlists' => $playlists
            ]
        ]);
    }
}
