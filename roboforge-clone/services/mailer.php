<?php
require_once __DIR__ . '/../config.php';

function roboforge_send_payment_email(string $subject, string $body): void
{
    $to = PAYMENTS_NOTIFICATION_EMAIL;
    $headers = 'From: RoboForge <no-reply@roboforge.local>';

    $sent = false;
    if (function_exists('mail')) {
        $sent = @mail($to, $subject, $body, $headers);
    }

    if (!$sent) {
        error_log('[RoboForge payments] Email to ' . $to . ': ' . $subject . ' — ' . $body);
    }
}
