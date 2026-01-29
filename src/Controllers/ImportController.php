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

            // 4. Téléchargement de la vidéo avec retry en cas d'erreur bot
            error_log("IMPORT: Starting download for video ID: {$foundVideo['id']}");
            
            $maxRetries = 2;
            $downloadResult = null;
            $lastError = null;
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                if ($attempt > 1) {
                    // Attendre 5 secondes avant de réessayer
                    error_log("IMPORT: Retry attempt {$attempt} after 5 seconds delay...");
                    sleep(5);
                }
                
                $downloadResult = $this->downloader->download($foundVideo['id'], "$spotifyArtist - $spotifyTitle");
                
                if ($downloadResult['success']) {
                    break; // Succès, on sort de la boucle
                }
                
                $lastError = $downloadResult['error'] ?? 'Unknown error';
                error_log("IMPORT: Download attempt {$attempt} failed: {$lastError}");
                
                // Si c'est une erreur bot et qu'on a encore des tentatives, on réessaye
                if (stripos($lastError, 'bot') === false && stripos($lastError, 'cookies') === false) {
                    // Ce n'est pas une erreur bot, pas la peine de réessayer
                    break;
                }
            }

            if (!$downloadResult['success']) {
                error_log("IMPORT: Download failed after {$maxRetries} attempts: {$lastError}");
                
                // Déterminer si c'est une erreur bot pour un message plus clair
                $isBotError = stripos($lastError, 'bot') !== false || stripos($lastError, 'cookies') !== false;
                
                json([
                    'success' => false,
                    'status' => $isBotError ? 'bot_detected' : 'download_failed',
                    'message' => $isBotError 
                        ? 'YouTube détecte un bot (trop de requêtes). Attendez quelques minutes.' 
                        : ($lastError ?? 'Échec du téléchargement'),
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
            // Nettoyer les titres et métadonnées
            $cleanTitle = $this->cleanTitle($metadata['title'] ?? $spotifyTitle);
            $cleanArtist = $this->cleanTitle($metadata['artist'] ?? $spotifyArtist);
            $cleanAlbum = isset($metadata['album']) ? $this->cleanTitle($metadata['album']) : null;
            
            $songId = $this->musicModel->create([
                'title' => $cleanTitle,
                'artist' => $cleanArtist,
                'album' => $cleanAlbum,
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
                    'title' => $cleanTitle,
                    'artist' => $cleanArtist,
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

    /**
     * Nettoie un titre en :
     * 1. Décodant les entités HTML (&#201; → É)
     * 2. Supprimant les parenthèses/crochets contenant "officiel", "official", "clip", "video", etc.
     * 3. Nettoyant les espaces multiples
     */
    private function cleanTitle(string $title): string
    {
        // 1. Décoder les entités HTML
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // 2. Supprimer les parenthèses/crochets contenant des mots-clés
        // Liste des mots-clés à détecter (insensible à la casse)
        $keywords = [
            'officiel', 'official', 'clip', 'video', 'audio', 'lyric', 'lyrics',
            'visualizer', 'visualiser', 'vevo', 'hd', '4k', 'music video',
            'official video', 'official audio', 'official music video',
            'clip officiel', 'vidéo officielle'
        ];
        
        // Pattern pour détecter (texte) ou [texte]
        foreach ($keywords as $keyword) {
            // Parenthèses
            $title = preg_replace('/\s*\([^)]*' . preg_quote($keyword, '/') . '[^)]*\)/ui', '', $title);
            // Crochets
            $title = preg_replace('/\s*\[[^\]]*' . preg_quote($keyword, '/') . '[^\]]*\]/ui', '', $title);
        }
        
        // 3. Nettoyer les espaces multiples et trim
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);
        
        // 4. Nettoyer les tirets orphelins à la fin (ex: "Titre - ")
        $title = preg_replace('/\s*[-–—]\s*$/', '', $title);
        
        return $title;
    }
}
