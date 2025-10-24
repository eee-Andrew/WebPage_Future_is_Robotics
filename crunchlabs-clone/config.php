<?php
// Global configuration constants for database access and shared session start.
define('DB_HOST', 'localhost');
define('DB_NAME', 'crunchlabs');
define('DB_USER', 'root'); // Update if your MySQL user differs.
define('DB_PASS', '');     // Fill with your MySQL password if set.

// Harden session cookies before the session starts.
if (!headers_sent()) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    session_set_cookie_params($cookieParams);
}

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

// Ensure a single session is started for auth checks across pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
