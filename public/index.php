<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/helpers.php';

use App\Core\Router;

// On charge le fichier .env s'il existe
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    // Si pas de .env (ex: prod sans fichier), on continue sans planter
    // mais dans ton cas, c'est ce qui va sauver la mise
    error_log("Note: .env file not found or could not be loaded.");
}

// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Créer le dossier de logs s'il n'existe pas
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Gestionnaire d'exceptions global pour capturer toutes les erreurs
set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage());
    error_log("Stack trace: " . $exception->getTraceAsString());
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error: ' . $exception->getMessage(),
        'trace' => $exception->getTraceAsString()
    ]);
    exit;
});

// Create router instance
$router = new Router();

// Define routes

// Home
$router->get('/', 'HomeController', 'index');

// Music routes
$router->get('/music', 'MusicController', 'index');
$router->get('/music/{id}', 'MusicController', 'show');
$router->get('/music/search', 'MusicController', 'search');
$router->get('/stream/{id}', 'MusicController', 'stream');

// Search/Download routes
$router->get('/search', 'SearchController', 'index');
$router->get('/api/youtube/search', 'SearchController', 'searchYoutube');
$router->post('/api/youtube/download', 'SearchController', 'download');
$router->get('/api/youtube/status', 'SearchController', 'status');

// Playlist routes
$router->get('/playlists', 'PlaylistController', 'index');
$router->get('/playlists/{id}', 'PlaylistController', 'show');
$router->post('/api/playlists/create', 'PlaylistController', 'create');
$router->post('/api/playlists/{id}/update', 'PlaylistController', 'update');
$router->post('/api/playlists/{id}/delete', 'PlaylistController', 'delete');
$router->post('/api/playlists/{id}/add-song', 'PlaylistController', 'addSong');
$router->post('/api/playlists/{id}/remove-song', 'PlaylistController', 'removeSong');

// API routes
$router->post('/api/music/scan', 'MusicController', 'scan');
$router->post('/api/music/{id}/delete', 'MusicController', 'delete');
$router->get('/api/music/{id}', 'ApiController', 'getSong');
$router->get('/api/playlists', 'ApiController', 'getAllPlaylists');

// Dispatch the request
$router->dispatch();
