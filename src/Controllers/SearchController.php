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
        try {
            // Vérifier le token CSRF
            csrf_verify();
            
            $videoId = input('video_id', '');
            $customTitle = input('custom_title', null);

            if (empty($videoId)) {
                json(['success' => false, 'error' => 'Video ID is required'], 400);
                return;
            }

            // Download the video directly
            $downloadResult = $this->downloader->download($videoId, $customTitle);

            if (!$downloadResult['success']) {
                json($downloadResult, 500);
                return;
            }

            // Extract metadata from the downloaded file
            $metadata = $this->musicModel->extractMetadata($downloadResult['file_path']);

            // Check if song already exists in database by file_path
            $existingSong = $this->musicModel->getByFilePath($downloadResult['file_path']);
            
            if ($existingSong) {
                // Song already exists, return its ID
                json([
                    'success' => true,
                    'message' => 'Song already exists in library',
                    'song_id' => $existingSong->id,
                    'already_exists' => true
                ]);
                return;
            }

            // Add to database
            $songId = $this->musicModel->create([
                'title' => $customTitle ?: $metadata['title'],
                'artist' => $metadata['artist'],
                'album' => 'YouTube Downloads',
                'file_path' => $downloadResult['file_path'],
                'duration' => $metadata['duration'],
                'youtube_id' => $videoId,
                'cover_path' => $downloadResult['cover_path'] ?? null
            ]);

            json([
                'success' => true,
                'message' => 'Download completed',
                'song_id' => $songId
            ]);
            
        } catch (\Exception $e) {
            error_log("Download error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
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
