<?php

/**
 * Render a view file
 */
function view(string $viewPath, array $data = []): void
{
    // Extract data to make variables available in view
    extract($data);

    $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';

    if (!file_exists($viewFile)) {
        die("View not found: {$viewPath}");
    }

    require $viewFile;
}

/**
 * Render a component with data
 */
function component(string $componentName, array $data = []): void
{
    extract($data);

    $componentFile = __DIR__ . '/../Views/components/' . $componentName . '.php';

    if (!file_exists($componentFile)) {
        die("Component not found: {$componentName}");
    }

    require $componentFile;
}

/**
 * Redirect to a path
 */
function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * Get asset URL
 */
function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

/**
 * Escape HTML output
 */
function e(?string $value): string
{
    return $value ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '';
}

/**
 * Get config value
 */
function config(string $key, $default = null)
{
    static $config = null;
    
    if ($config === null) {
        $config = require __DIR__ . '/../../config.php';
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }

    return $value;
}

/**
 * Return JSON response
 */
function json($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get POST/GET data safely
 */
function input(string $key, $default = null)
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Get CSRF token
 */
function csrf_token(): string
{
    return \App\Core\CSRF::getToken();
}

/**
 * Get CSRF token field
 */
function csrf_field(): string
{
    return \App\Core\CSRF::field();
}

/**
 * Get CSRF meta tag
 */
function csrf_meta(): string
{
    return \App\Core\CSRF::metaTag();
}

/**
 * Verify CSRF token
 */
function csrf_verify(): void
{
    \App\Core\CSRF::verify();
}
