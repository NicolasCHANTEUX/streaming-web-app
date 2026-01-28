<?php
// Router for PHP built-in server

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $requestUri;

// Serve static files directly
if ($requestUri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // Serve the requested resource as-is
}

// Otherwise, route through index.php
require __DIR__ . '/index.php';
