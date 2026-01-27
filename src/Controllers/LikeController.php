<?php

namespace App\Controllers;

use App\Models\Like;
use App\Models\Music;

class LikeController
{
    private Like $likeModel;
    private Music $musicModel;

    public function __construct()
    {
        $this->likeModel = new Like();
        $this->musicModel = new Music();
    }

    public function index(): void
    {
        $songs = $this->likeModel->getLikedSongs();
        
        view('pages/liked', [
            'title' => 'Titres Likés - Music Streaming',
            'songs' => $songs
        ]);
    }

    public function toggle(string $id): void
    {
        csrf_verify();
        
        $songId = (int)$id;
        $song = $this->musicModel->getById($songId);
        
        if (!$song) {
            json(['success' => false, 'error' => 'Song not found'], 404);
            return;
        }
        
        $result = $this->likeModel->toggle($songId);
        
        json([
            'success' => true,
            'liked' => $result['liked']
        ]);
    }

    public function check(string $id): void
    {
        $songId = (int)$id;
        $isLiked = $this->likeModel->isLiked($songId);
        
        json([
            'liked' => $isLiked
        ]);
    }
}
