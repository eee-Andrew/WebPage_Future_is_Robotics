<?php
// Global configuration constants for database access and shared session start.
define('DB_HOST', 'localhost');
define('DB_NAME', 'crunchlabs');
define('DB_USER', 'root'); // Update if your MySQL user differs.
define('DB_PASS', '');     // Fill with your MySQL password if set.

// Ensure a single session is started for auth checks across pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
