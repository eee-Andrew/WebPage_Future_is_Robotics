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
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request payload.']);
    exit;
}

$shipping = $payload['shipping'] ?? [];
$requiredFields = ['full_name', 'address_line1', 'city', 'postal_code', 'country', 'email', 'phone'];
foreach ($requiredFields as $field) {
    $value = trim((string) ($shipping[$field] ?? ''));
    if ($value === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Please complete all required shipping fields.']);
        exit;
    }
    $shipping[$field] = $value;
}

if (empty($payload['paymentMethod']) || !is_array($payload['paymentMethod'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payment method data is missing.']);
    exit;
}

try {
    $pdo = roboforge_db();
    $currentUser = roboforge_current_user();

    $cartStmt = $pdo->prepare('SELECT p.id, p.name, p.price, c.quantity FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
    $cartStmt->execute([$currentUser['id']]);
    $cartItems = $cartStmt->fetchAll();

    if (!$cartItems) {
        http_response_code(400);
        echo json_encode(['error' => 'Your cart is empty.']);
        exit;
    }

    $lineItems = [];
    $total = 0.0;
    foreach ($cartItems as $item) {
        $price = (float) $item['price'];
        $quantity = (int) $item['quantity'];
        $total += $price * $quantity;
        $description = $item['name'];
        $description = function_exists('mb_substr') ? mb_substr($description, 0, 50) : substr($description, 0, 50);
        $lineItems[] = [
            'id' => (string) $item['id'],
            'description' => $description,
            'quantity' => $quantity,
            'amountIncludingTax' => roboforge_adyen_amount_minor($price * $quantity),
            'taxAmount' => 0,
        ];
    }

    $amountMinor = roboforge_adyen_amount_minor($total);
    $shopperReference = roboforge_adyen_get_or_create_shopper($pdo, $currentUser['id'], $shipping['email'], $shipping['country']);

    $fullName = $shipping['full_name'];
    $nameParts = preg_split('/\s+/', $fullName, 2);
    $firstName = $nameParts[0] ?? $fullName;
    $lastName = $nameParts[1] ?? ($nameParts[0] ?? '');

    $countryCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $shipping['country']), 0, 2));
    if (strlen($countryCode) !== 2) {
        $countryCode = 'GR';
    }

    $orderTotal = number_format($total, 2, '.', '');

    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, status, total, currency, full_name, address_line1, address_line2, city, postal_code, country, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $orderStmt->execute([
        $currentUser['id'],
        'PendingPayment',
        $orderTotal,
        ROBOFORGE_CURRENCY,
        $shipping['full_name'],
        $shipping['address_line1'],
        $shipping['address_line2'] ?? '',
        $shipping['city'],
        $shipping['postal_code'],
        $shipping['country'],
        $shipping['email'],
        $shipping['phone'],
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_each) VALUES (?, ?, ?, ?)');
    foreach ($cartItems as $item) {
        $itemStmt->execute([
            $orderId,
            (int) $item['id'],
            (int) $item['quantity'],
            (float) $item['price'],
        ]);
    }

    $paymentId = roboforge_uuid();
    $merchantReference = 'ROBOFORGE-' . $orderId . '-' . strtoupper(substr(str_replace('-', '', $paymentId), 0, 6));

    $paymentStmt = $pdo->prepare('INSERT INTO payments (id, order_id, merchant_reference, amount_minor, currency) VALUES (?, ?, ?, ?, ?)');
    $paymentStmt->execute([
        $paymentId,
        $orderId,
        $merchantReference,
        $amountMinor,
        ROBOFORGE_CURRENCY,
    ]);

    $pdo->commit();

    $browserInfo = $payload['browserInfo'] ?? null;

    $addressPayload = [
        'street' => $shipping['address_line1'],
        'houseNumberOrName' => $shipping['address_line2'] ?? 'N/A',
        'postalCode' => $shipping['postal_code'],
        'city' => $shipping['city'],
        'country' => $countryCode,
    ];

    $adyenPayload = [
        'merchantAccount' => ADYEN_MERCHANT_ACCOUNT,
        'amount' => [
            'currency' => ROBOFORGE_CURRENCY,
            'value' => $amountMinor,
        ],
        'reference' => $merchantReference,
        'shopperReference' => $shopperReference,
        'shopperEmail' => $shipping['email'],
        'shopperName' => [
            'firstName' => $firstName,
            'lastName' => $lastName,
        ],
        'shopperIP' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'shopperLocale' => 'en-US',
        'returnUrl' => roboforge_url('cart.php?payment=' . urlencode($paymentId)),
        'channel' => 'Web',
        'lineItems' => $lineItems,
        'additionalData' => [
            'allow3DS2' => 'true',
        ],
        'paymentMethod' => $payload['paymentMethod'],
        'browserInfo' => $browserInfo,
        'billingAddress' => $addressPayload,
        'deliveryAddress' => $addressPayload,
        'storePaymentMethod' => !empty($payload['storePaymentMethod']),
    ];

    $adyenPayload = array_filter($adyenPayload, function ($value) {
        return $value !== null && $value !== '';
    });

    $paymentResponse = roboforge_adyen_api_request('payments', $adyenPayload);

    $status = 'Pending';
    $resultCode = $paymentResponse['resultCode'] ?? 'Error';
    if ($resultCode === 'Authorised') {
        $status = 'Authorised';
    } elseif ($resultCode === 'Refused') {
        $status = 'Refused';
    } elseif ($resultCode === 'Cancelled') {
        $status = 'Cancelled';
    } elseif ($resultCode === 'Error') {
        $status = 'Error';
    }

    $paymentUpdate = $pdo->prepare('UPDATE payments SET adyen_psp_reference = ?, status = ?, result_code = ?, payment_method_type = ?, three_ds_result = ?, updated_at = NOW() WHERE id = ?');
    $paymentUpdate->execute([
        $paymentResponse['pspReference'] ?? null,
        $status,
        $resultCode,
        $paymentResponse['additionalData']['paymentMethod'] ?? null,
        $paymentResponse['additionalData']['threeds2.result'] ?? null,
        $paymentId,
    ]);

    if (!empty($payload['storePaymentMethod']) && !empty($paymentResponse['additionalData'])) {
        roboforge_adyen_store_token($paymentResponse['additionalData'], $shopperReference);
    }

    if ($status === 'Authorised') {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Paid', $orderId]);
        $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$currentUser['id']]);
    } elseif ($status === 'Refused') {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Failed', $orderId]);
    }

    $responsePayload = [
        'paymentId' => $paymentId,
        'orderId' => $orderId,
        'resultCode' => $resultCode,
        'status' => $status,
    ];

    if (!empty($paymentResponse['action'])) {
        $responsePayload['action'] = $paymentResponse['action'];
    }

    if (!empty($paymentResponse['pspReference'])) {
        $responsePayload['pspReference'] = $paymentResponse['pspReference'];
    }

    echo json_encode($responsePayload);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (isset($pdo, $paymentId)) {
        $update = $pdo->prepare('UPDATE payments SET status = ?, result_code = ?, updated_at = NOW() WHERE id = ?');
        $update->execute(['Error', 'Error', $paymentId]);
    }
    if (isset($pdo, $orderId)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['Failed', $orderId]);
    }
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()]);
}
