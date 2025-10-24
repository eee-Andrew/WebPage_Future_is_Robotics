<?php
// Global configuration constants for database access.
define('DB_HOST', 'localhost');
define('DB_NAME', 'crunchlabs');
define('DB_USER', 'root'); // Update if your MySQL user differs.
define('DB_PASS', '');     // Fill with your MySQL password if set.

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

if (!function_exists('crunchlabs_url')) {
    function crunchlabs_url(string $path = ''): string
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

if (!function_exists('crunchlabs_public_path')) {
    function crunchlabs_public_path(?string $path, ?string $fallback = null): string
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

        return crunchlabs_url($path);
    }
}
