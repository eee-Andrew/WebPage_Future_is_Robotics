<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../services/adyen.php';

header('Content-Type: application/json');

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

try {
    $pdo = roboforge_db();
    $currentUser = roboforge_current_user();

    $sumStmt = $pdo->prepare('SELECT SUM(c.quantity * p.price) AS total FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
    $sumStmt->execute([$currentUser['id']]);
    $total = (float) $sumStmt->fetchColumn();

    if ($total <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Your cart is empty.']);
        exit;
    }

    $amountMinor = roboforge_adyen_amount_minor($total);
    $shopperReference = roboforge_adyen_get_or_create_shopper($pdo, $currentUser['id'], $currentUser['email']);

    $paymentMethods = roboforge_adyen_api_request('paymentMethods', [
        'merchantAccount' => ADYEN_MERCHANT_ACCOUNT,
        'amount' => [
            'currency' => ROBOFORGE_CURRENCY,
            'value' => $amountMinor,
        ],
        'channel' => 'Web',
        'shopperReference' => $shopperReference,
    ]);

    echo json_encode([
        'environment' => ADYEN_ENVIRONMENT === 'live' ? 'live' : 'test',
        'clientKey' => ADYEN_CLIENT_KEY,
        'amount' => [
            'currency' => ROBOFORGE_CURRENCY,
            'value' => $amountMinor,
        ],
        'locale' => 'en-US',
        'paymentMethodsResponse' => $paymentMethods,
    ]);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()]);
}
