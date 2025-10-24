<?php
require_once __DIR__ . '/config.php';

$_SESSION = [];
if (function_exists('session_regenerate_id')) {
    session_regenerate_id(true);
}

crunchlabs_flash_set('success', 'You have been signed out.');
crunchlabs_redirect('index.php');
