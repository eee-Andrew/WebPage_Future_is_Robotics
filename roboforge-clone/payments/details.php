<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../services/adyen.php';
require_once __DIR__ . '/../services/mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

if (!roboforge_is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

if (!roboforge_adyen_is_configured()) {
    http_response_code(503);
    echo json_encode(['error' => 'Adyen configuration is incomplete.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload) || empty($payload['paymentId']) || empty($payload['details']) || !is_array($payload['details'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request payload.']);
    exit;
}

try {
    $pdo = roboforge_db();
    $currentUser = roboforge_current_user();

    $paymentStmt = $pdo->prepare('SELECT payments.*, orders.user_id, orders.email FROM payments JOIN orders ON orders.id = payments.order_id WHERE payments.id = ? LIMIT 1');
    $paymentStmt->execute([$payload['paymentId']]);
    $payment = $paymentStmt->fetch();

    if (!$payment || (int) $payment['user_id'] !== (int) $currentUser['id']) {
        http_response_code(404);
        echo json_encode(['error' => 'Payment not found.']);
        exit;
    }

    $adyenResponse = roboforge_adyen_api_request('payments/details', $payload['details']);

    $resultCode = $adyenResponse['resultCode'] ?? 'Error';
    $status = 'Pending';
    if ($resultCode === 'Authorised') {
        $status = 'Authorised';
    } elseif ($resultCode === 'Refused') {
        $status = 'Refused';
    } elseif ($resultCode === 'Cancelled') {
        $status = 'Cancelled';
    } elseif ($resultCode === 'Error') {
        $status = 'Error';
    }

    $update = $pdo->prepare('UPDATE payments SET status = ?, result_code = ?, payment_method_type = ?, adyen_psp_reference = ?, three_ds_result = ?, updated_at = NOW() WHERE id = ?');
    $update->execute([
        $status,
        $resultCode,
        $adyenResponse['additionalData']['paymentMethod'] ?? $payment['payment_method_type'],
        $adyenResponse['pspReference'] ?? $payment['adyen_psp_reference'],
        $adyenResponse['additionalData']['threeds2.result'] ?? null,
        $payment['id'],
    ]);

    $shopperReference = roboforge_adyen_get_or_create_shopper($pdo, (int) $currentUser['id'], $payment['email'] ?? $currentUser['email']);
    if (!empty($adyenResponse['additionalData'])) {
        roboforge_adyen_store_token($adyenResponse['additionalData'], $shopperReference);
    }

    if ($status === 'Authorised') {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Paid', $payment['order_id']]);
        $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$currentUser['id']]);
    } elseif ($status === 'Refused') {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Failed', $payment['order_id']]);
    }

    $responsePayload = [
        'paymentId' => $payment['id'],
        'orderId' => $payment['order_id'],
        'resultCode' => $resultCode,
        'status' => $status,
    ];

    if (!empty($adyenResponse['pspReference'])) {
        $responsePayload['pspReference'] = $adyenResponse['pspReference'];
    }

    echo json_encode($responsePayload);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()]);
}
