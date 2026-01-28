<?php

namespace App\Core;

class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Generate a new CSRF token
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;

        return $token;
    }

    /**
     * Get the current CSRF token, generate if not exists
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return self::generateToken();
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::TOKEN_NAME]) || !$token) {
            return false;
        }

        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    /**
     * Verify CSRF token from request and terminate if invalid
     */
    public static function verify(): void
    {
        error_log("=== CSRF VERIFY DEBUG ===");
        error_log("Session ID: " . session_id());
        error_log("Session token: " . ($_SESSION[self::TOKEN_NAME] ?? 'not set'));
        error_log("POST token: " . ($_POST[self::TOKEN_NAME] ?? 'not set'));
        error_log("HTTP_X_CSRF_TOKEN: " . ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'not set'));
        
        // Check multiple sources for the token
        $token = $_POST[self::TOKEN_NAME] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? (function_exists('apache_request_headers') ? (apache_request_headers()['X-CSRF-Token'] ?? null) : null);
        
        error_log("Final token used: " . ($token ?? 'NULL'));

        if (!self::validateToken($token)) {
            error_log("CSRF validation FAILED");
            http_response_code(403);
            json([
                'success' => false,
                'error' => 'Invalid CSRF token'
            ]);
            exit;
        }
        
        error_log("CSRF validation SUCCESS");
    }

    /**
     * Get HTML input field for CSRF token
     */
    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Get meta tag for CSRF token (for AJAX requests)
     */
    public static function metaTag(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}
