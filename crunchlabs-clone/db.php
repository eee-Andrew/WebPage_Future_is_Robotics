<?php
require_once __DIR__ . '/config.php';

/**
 * Create a fresh PDO connection using the configured credentials.
 */
function crunchlabs_create_connection(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);
}

/**
 * Return a cached PDO connection, automatically re-establishing it when the
 * MySQL server drops the link ("server has gone away").
 */
function crunchlabs_db(): PDO
{
    static $pdo = null;

    if (!($pdo instanceof PDO)) {
        $pdo = crunchlabs_create_connection();
        return $pdo;
    }

    try {
        $pdo->query('SELECT 1');
    } catch (PDOException $e) {
        $message = $e->getMessage();
        $errorCode = (int) $e->getCode();

        // Reconnect when MySQL closes the connection (error 2006) or reports
        // a truncated packet (error 2013) which indicates a dropped link.
        if ($errorCode === 2006 || $errorCode === 2013 || stripos($message, 'server has gone away') !== false) {
            $pdo = crunchlabs_create_connection();
        } else {
            throw $e;
        }
    }

    return $pdo;
}

try {
    $pdo = crunchlabs_db();
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}
