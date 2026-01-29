<?php

namespace App\Controllers;

use App\Models\YoutubeDownloader;
use App\Models\Music;

class ImportController
{
    private YoutubeDownloader $downloader;
    private Music $musicModel;

    public function __construct()
    {
        $this->downloader = new YoutubeDownloader();
        $this->musicModel = new Music();
    }

    /**
     * Affiche la page d'importation
     */
    public function index(): void
    {
        view('layouts/main', [
            'title' => 'Import Spotify CSV',
            'content' => 'pages/import',
            'data' => []
        ]);
    }

    /**
     * Recherche une musique sur YouTube avec filtrage strict et télécharge si trouvée
     * Critères : Le titre YouTube doit contenir le nom de la musique ET "official"/"officiel"
     */
    public function searchAndDownload(): void
    {
        error_log("=== IMPORT: Search and Download START ===");

        // Vérifier le token CSRF
        csrf_verify();
        
        // Récupération des données JSON
        $data = json_decode(file_get_contents('php://input'), true);
        $spotifyTitle = trim($data['title'] ?? '');
        $spotifyArtist = trim($data['artist'] ?? '');
        $spotifyUri = $data['uri'] ?? '';

        error_log("IMPORT: Track=\"$spotifyTitle\", Artist=\"$spotifyArtist\", URI=\"$spotifyUri\"");

        if (empty($spotifyTitle) || empty($spotifyArtist)) {
            json(['success' => false, 'status' => 'error', 'message' => 'Données manquantes (titre ou artiste)']);
            return;
        }

        try {
            // 1. Recherche YouTube avec "Artiste + Titre"
            $query = "$spotifyArtist $spotifyTitle";
            error_log("IMPORT: Searching YouTube with query: $query");
            
            $results = $this->downloader->search($query, 10);
            error_log("IMPORT: YouTube returned " . count($results) . " results");

            if (empty($results)) {
                error_log("IMPORT: No results found");
                json([
                    'success' => false,
                    'status' => 'no_results',
                    'message' => 'Aucun résultat YouTube trouvé',
                    'query' => $query
                ]);
                return;
            }

            // 2. Filtrage Strict : Trouver la première vidéo qui contient le titre ET "official"/"officiel"
            $foundVideo = null;

            foreach ($results as $video) {
                $youtubeTitle = $video['title'];
                
                error_log("IMPORT: Checking video: $youtubeTitle");

                // Condition A : Le titre YouTube contient le nom de la musique (case insensitive)
                $hasTitle = stripos($youtubeTitle, $spotifyTitle) !== false;

                // Condition B : Contient "official" ou "officiel"
                $isOfficial = (stripos($youtubeTitle, 'official') !== false) || 
                              (stripos($youtubeTitle, 'officiel') !== false);

                error_log("IMPORT: hasTitle=$hasTitle, isOfficial=$isOfficial");

                if ($hasTitle && $isOfficial) {
                    $foundVideo = $video;
                    error_log("IMPORT: ✓ Match found! Video: $youtubeTitle");
                    break;
                }
            }

            if (!$foundVideo) {
                error_log("IMPORT: No official video found matching criteria");
                json([
                    'success' => false,
                    'status' => 'skipped',
                    'message' => 'Aucune vidéo officielle trouvée',
                    'query' => $query,
                    'results_count' => count($results)
                ]);
                return;
            }

            // 3. Vérifier si la musique existe déjà en base
            // On cherche par titre + artiste pour éviter les doublons
            $existingSong = $this->musicModel->searchByTitleAndArtist($spotifyTitle, $spotifyArtist);
            
            if ($existingSong) {
                error_log("IMPORT: Song already exists in database (ID: {$existingSong->id})");
                json([
                    'success' => true,
                    'status' => 'already_exists',
                    'message' => 'Déjà dans la bibliothèque',
                    'video' => $foundVideo['title'],
                    'song_id' => $existingSong->id
                ]);
                return;
            }

            // 4. Téléchargement de la vidéo
            error_log("IMPORT: Starting download for video ID: {$foundVideo['id']}");
            
            $downloadResult = $this->downloader->download($foundVideo['id'], "$spotifyArtist - $spotifyTitle");

            if (!$downloadResult['success']) {
                error_log("IMPORT: Download failed: " . ($downloadResult['error'] ?? 'Unknown error'));
                json([
                    'success' => false,
                    'status' => 'download_failed',
                    'message' => $downloadResult['error'] ?? 'Échec du téléchargement',
                    'video' => $foundVideo['title']
                ]);
                return;
            }

            // 5. Extraction des métadonnées et ajout en base
            $metadata = $this->musicModel->extractMetadata($downloadResult['file_path']);

            // Normaliser les chemins
            $normalizedFilePath = str_replace('/', DIRECTORY_SEPARATOR, $downloadResult['file_path']);
            $normalizedCoverPath = isset($downloadResult['cover_path']) 
                ? str_replace('/', DIRECTORY_SEPARATOR, $downloadResult['cover_path'])
                : null;

            // Vérification finale par file_path
            $existingByPath = $this->musicModel->getByFilePath($normalizedFilePath);
            
            if ($existingByPath) {
                error_log("IMPORT: Song already exists by file path (ID: {$existingByPath->id})");
                json([
                    'success' => true,
                    'status' => 'already_exists',
                    'message' => 'Déjà dans la bibliothèque',
                    'video' => $foundVideo['title'],
                    'song_id' => $existingByPath->id
                ]);
                return;
            }

            // 6. Insertion en base de données
            $songId = $this->musicModel->create([
                'title' => $metadata['title'] ?? $spotifyTitle,
                'artist' => $metadata['artist'] ?? $spotifyArtist,
                'album' => $metadata['album'] ?? null,
                'duration' => $metadata['duration'] ?? $foundVideo['duration'] ?? 0,
                'file_path' => $normalizedFilePath,
                'cover_path' => $normalizedCoverPath,
                'youtube_id' => $foundVideo['id']
            ]);

            error_log("IMPORT: ✓ Success! Song added to database with ID: $songId");

            json([
                'success' => true,
                'status' => 'downloaded',
                'message' => 'Téléchargé avec succès',
                'video' => $foundVideo['title'],
                'song_id' => $songId,
                'metadata' => [
                    'title' => $metadata['title'] ?? $spotifyTitle,
                    'artist' => $metadata['artist'] ?? $spotifyArtist,
                    'duration' => $metadata['duration'] ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            error_log("IMPORT ERROR: " . $e->getMessage());
            error_log("IMPORT TRACE: " . $e->getTraceAsString());
            
            json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
