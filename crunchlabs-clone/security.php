<?php
require_once __DIR__ . '/config.php';

if (!function_exists('get_csrf_token')) {
    function get_csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('regenerate_csrf_token')) {
    function regenerate_csrf_token(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token(?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        return is_string($sessionToken) && hash_equals($sessionToken, $token);
    }
}
