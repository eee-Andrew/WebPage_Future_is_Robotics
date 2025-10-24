<?php
// Global configuration constants for database access.
define('DB_HOST', 'localhost');
define('DB_NAME', 'roboforge');
define('DB_USER', 'root'); // Update if your MySQL user differs.
define('DB_PASS', '');     // Fill with your MySQL password if set.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!defined('APP_BASE_PATH')) {
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $documentRootReal = $documentRoot !== '' ? realpath($documentRoot) : false;
    $projectRoot = realpath(__DIR__);

    $documentRootNormalized = $documentRootReal !== false
        ? rtrim(str_replace('\\', '/', $documentRootReal), '/')
        : '';
    $projectRootNormalized = $projectRoot !== false
        ? str_replace('\\', '/', $projectRoot)
        : '';

    $basePath = '';

    if ($documentRootNormalized !== '' && $projectRootNormalized !== ''
        && strpos($projectRootNormalized, $documentRootNormalized) === 0) {
        $basePath = trim(substr($projectRootNormalized, strlen($documentRootNormalized)), '/');
    } elseif ($projectRootNormalized !== '') {
        $basePath = basename($projectRootNormalized);
    }

    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    } else {
        $basePath = '/' . $basePath;
    }

    define('APP_BASE_PATH', $basePath);
}

if (!function_exists('roboforge_url')) {
    function roboforge_url(string $path = ''): string
    {
        $base = APP_BASE_PATH;
        $trimmed = ltrim($path, '/');

        if ($trimmed === '') {
            return $base === '' ? '/' : $base . '/';
        }

        if ($base === '') {
            return '/' . $trimmed;
        }

        return $base . '/' . $trimmed;
    }
}

if (!function_exists('roboforge_public_path')) {
    function roboforge_public_path(?string $path, ?string $fallback = null): string
    {
        if ($path === null || $path === '') {
            return $fallback ?? '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if ($path[0] === '/') {
            return $path;
        }

        return roboforge_url($path);
    }
}

if (!function_exists('roboforge_is_logged_in')) {
    function roboforge_is_logged_in(): bool
    {
        return !empty($_SESSION['user']);
    }
}

if (!function_exists('roboforge_current_user')) {
    function roboforge_current_user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('roboforge_require_login')) {
    function roboforge_require_login(?string $redirectTo = null): void
    {
        if (roboforge_is_logged_in()) {
            return;
        }

        $target = $redirectTo ?? ($_SERVER['REQUEST_URI'] ?? roboforge_url());
        $destination = roboforge_url('login.php?redirect=' . urlencode($target));
        header('Location: ' . $destination);
        exit;
    }
}

if (!function_exists('roboforge_redirect')) {
    function roboforge_redirect(string $path): void
    {
        if (preg_match('#^https?://#i', $path)) {
            $parsed = parse_url($path);
            $path = ($parsed['path'] ?? '/');
            if (!empty($parsed['query'])) {
                $path .= '?' . $parsed['query'];
            }
        }

        if ($path !== '' && $path[0] === '/' && (APP_BASE_PATH === '' || strpos($path, APP_BASE_PATH) === 0)) {
            header('Location: ' . $path);
            exit;
        }

        header('Location: ' . roboforge_url(ltrim($path, '/')));
        exit;
    }
}

if (!function_exists('roboforge_flash_set')) {
    function roboforge_flash_set(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('roboforge_flash_get')) {
    function roboforge_flash_get(string $key): ?string
    {
        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
}
