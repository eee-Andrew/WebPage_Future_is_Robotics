<?php
require_once __DIR__ . '/config.php';

$_SESSION = [];
if (function_exists('session_regenerate_id')) {
    session_regenerate_id(true);
}

roboforge_flash_set('success', 'You have been signed out.');
roboforge_redirect('index.php');
