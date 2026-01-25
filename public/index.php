<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/helpers.php';

use App\Core\Router;

// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
