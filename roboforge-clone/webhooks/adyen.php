<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../services/adyen.php';
require_once __DIR__ . '/../services/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

if (ADYEN_HMAC_KEY === '') {
    http_response_code(503);
    exit('Webhook HMAC key not configured');
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload) || empty($payload['notificationItems']) || !is_array($payload['notificationItems'])) {
    http_response_code(400);
    exit('Invalid payload');
}

$pdo = roboforge_db();

foreach ($payload['notificationItems'] as $item) {
    if (!isset($item['NotificationRequestItem'])) {
        continue;
    }
    $notification = $item['NotificationRequestItem'];
    $eventId = roboforge_uuid();

    $storedNotification = $notification;
    unset($storedNotification['additionalData'], $storedNotification['billingAddress'], $storedNotification['deliveryAddress']);

    $eventStmt = $pdo->prepare('INSERT INTO webhook_events (id, event_date, event_code, success, psp_reference, original_reference, payload, processed) VALUES (?, ?, ?, ?, ?, ?, ?, 0)');
    $eventStmt->execute([
        $eventId,
        $notification['eventDate'] ?? date('Y-m-d H:i:s'),
        $notification['eventCode'] ?? 'UNKNOWN',
        isset($notification['success']) && $notification['success'] === 'true' ? 1 : 0,
        $notification['pspReference'] ?? '',
        $notification['originalReference'] ?? null,
        json_encode($storedNotification, JSON_UNESCAPED_SLASHES),
    ]);

    if (!roboforge_adyen_verify_hmac($item)) {
        continue;
    }

    $eventCode = strtoupper($notification['eventCode'] ?? '');
    $success = isset($notification['success']) && $notification['success'] === 'true';
    $merchantReference = $notification['merchantReference'] ?? '';
    if ($merchantReference === '') {
        continue;
    }

    $alreadyStmt = $pdo->prepare('SELECT 1 FROM webhook_events WHERE psp_reference = ? AND event_code = ? AND processed = 1 LIMIT 1');
    $alreadyStmt->execute([
        $notification['pspReference'] ?? '',
        $eventCode,
    ]);
    if ($alreadyStmt->fetchColumn()) {
        $pdo->prepare('UPDATE webhook_events SET processed = 1, processed_at = NOW() WHERE id = ?')->execute([$eventId]);
        continue;
    }

    $paymentStmt = $pdo->prepare('SELECT payments.*, orders.user_id, orders.email FROM payments JOIN orders ON orders.id = payments.order_id WHERE payments.merchant_reference = ? LIMIT 1');
    $paymentStmt->execute([$merchantReference]);
    $payment = $paymentStmt->fetch();
    if (!$payment) {
        continue;
    }

    $status = $payment['status'];
    $resultCode = $payment['result_code'];

    if ($eventCode === 'AUTHORISATION') {
        if ($success) {
            $status = 'Authorised';
            $resultCode = 'Authorised';
        } else {
            $status = 'Refused';
            $resultCode = $notification['reason'] ?? 'Refused';
        }
    } elseif ($eventCode === 'CAPTURE') {
        if ($success) {
            $status = 'Authorised';
        }
    } elseif ($eventCode === 'CANCELLATION') {
        $status = 'Cancelled';
        $resultCode = 'Cancelled';
    } elseif ($eventCode === 'REFUND') {
        $status = $success ? 'Refunded' : 'Error';
        $resultCode = $success ? 'Refunded' : 'RefundFailed';
    } elseif ($eventCode === 'CHARGEBACK') {
        $status = 'Chargeback';
        $resultCode = 'Chargeback';
    } elseif ($eventCode === 'CHARGEBACK_REVERSED') {
        $status = 'Authorised';
        $resultCode = 'ChargebackReversed';
    }

    $update = $pdo->prepare('UPDATE payments SET status = ?, result_code = ?, adyen_psp_reference = ?, updated_at = NOW() WHERE id = ?');
    $update->execute([
        $status,
        $resultCode,
        $notification['pspReference'] ?? $payment['adyen_psp_reference'],
        $payment['id'],
    ]);

    if ($status === 'Authorised') {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Paid', $payment['order_id']]);
        $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([(int) $payment['user_id']]);
        if ($resultCode === 'ChargebackReversed') {
            $subject = '[Payments] Chargeback Reversed — ' . $merchantReference;
            $body = 'Chargeback reversed. PSP ref: ' . ($notification['pspReference'] ?? '') . '. ' . ($notification['amount']['value'] ?? '0') . ' ' . ($notification['amount']['currency'] ?? ROBOFORGE_CURRENCY) . '.';
        } else {
            $subject = '[Payments] Authorised — ' . $merchantReference;
            $body = 'Order ' . $merchantReference . ' authorised. PSP ref: ' . ($notification['pspReference'] ?? '') . '. ' . ($notification['amount']['value'] ?? '0') . ' ' . ($notification['amount']['currency'] ?? ROBOFORGE_CURRENCY) . '. Method: ' . ($notification['paymentMethod'] ?? '');
        }
        roboforge_send_payment_email($subject, $body);
    } elseif ($status === 'Refused') {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Failed', $payment['order_id']]);
        $subject = '[Payments] Refused — ' . $merchantReference;
        $body = 'Order ' . $merchantReference . ' refused. Reason: ' . ($resultCode ?? 'Refused') . '. PSP ref: ' . ($notification['pspReference'] ?? '');
        roboforge_send_payment_email($subject, $body);
    } elseif ($status === 'Refunded') {
        $subject = '[Payments] Refund — ' . $merchantReference;
        $body = 'Refund ' . ($success ? 'completed' : 'failed') . '. Original PSP: ' . ($notification['originalReference'] ?? '') . '. Refund PSP: ' . ($notification['pspReference'] ?? '') . '. ' . ($notification['amount']['value'] ?? '0') . ' ' . ($notification['amount']['currency'] ?? ROBOFORGE_CURRENCY) . '.';
        roboforge_send_payment_email($subject, $body);
    } elseif ($status === 'Chargeback') {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Failed', $payment['order_id']]);
        $subject = '[Payments] Chargeback — ' . $merchantReference;
        $body = 'Chargeback received. Original PSP: ' . ($notification['originalReference'] ?? '') . '. Chargeback PSP: ' . ($notification['pspReference'] ?? '') . '. ' . ($notification['amount']['value'] ?? '0') . ' ' . ($notification['amount']['currency'] ?? ROBOFORGE_CURRENCY) . '.';
        roboforge_send_payment_email($subject, $body);
    }

    $pdo->prepare('UPDATE webhook_events SET processed = 1, processed_at = NOW() WHERE id = ?')->execute([$eventId]);
}

echo '[accepted]';
