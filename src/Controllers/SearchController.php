<?php

namespace App\Controllers;

use App\Models\YoutubeDownloader;
use App\Models\Music;

class SearchController
{
    private YoutubeDownloader $downloader;
    private Music $musicModel;

    public function __construct()
    {
        $this->downloader = new YoutubeDownloader();
        $this->musicModel = new Music();
    }

    public function index(): void
    {
        view('layouts/main', [
            'title' => 'Search YouTube',
            'content' => 'pages/search',
            'data' => []
        ]);
    }

    public function searchYoutube(): void
    {
        $query = input('q', '');

        if (empty($query)) {
            json(['success' => false, 'error' => 'Query is required'], 400);
            return;
        }

        $results = $this->downloader->search($query, 10);

        json(['success' => true, 'results' => $results]);
    }

    public function download(): void
    {
        // Vérifier le token CSRF
        csrf_verify();
        
        $videoId = input('video_id', '');
        $customTitle = input('custom_title', null);

        if (empty($videoId)) {
            json(['success' => false, 'error' => 'Video ID is required'], 400);
            return;
        }

        // Get video info first
        $videoInfo = $this->downloader->getVideoInfo($videoId);

        if (!$videoInfo) {
            json(['success' => false, 'error' => 'Could not fetch video info'], 500);
            return;
        }

        // Download the video
        $downloadResult = $this->downloader->download($videoId, $customTitle);

        if (!$downloadResult['success']) {
            json($downloadResult, 500);
            return;
        }

        // Add to database
        $songId = $this->musicModel->create([
            'title' => $customTitle ?? $videoInfo['title'],
            'artist' => $videoInfo['artist'],
            'album' => 'YouTube Downloads',
            'file_path' => $downloadResult['file_path'],
            'duration' => $videoInfo['duration'],
            'youtube_id' => $videoId,
            'cover_path' => $downloadResult['cover_path'] ?? null
        ]);

        json([
            'success' => true,
            'message' => 'Download completed',
            'song_id' => $songId
        ]);
    }

    public function status(): void
    {
        $isAvailable = $this->downloader->isAvailable();
        $version = $this->downloader->getVersion();

        json([
            'available' => $isAvailable,
            'version' => $version
        ]);
    }
}
