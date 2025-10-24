<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

function roboforge_adyen_is_configured(): bool
{
    return ADYEN_API_KEY !== '' && ADYEN_CLIENT_KEY !== '' && ADYEN_MERCHANT_ACCOUNT !== '';
}

function roboforge_adyen_checkout_base(): string
{
    $env = ADYEN_ENVIRONMENT === 'live' ? 'live' : 'test';
    return sprintf('https://checkout-%s.adyen.com/%s', $env, ADYEN_CHECKOUT_VERSION);
}

function roboforge_adyen_sdk_base(): string
{
    $env = ADYEN_ENVIRONMENT === 'live' ? 'live' : 'test';
    return sprintf('https://checkoutshopper-%s.adyen.com/checkoutshopper/sdk/%s', $env, ADYEN_SDK_VERSION);
}

function roboforge_adyen_api_request(string $endpoint, array $payload): array
{
    if (!roboforge_adyen_is_configured()) {
        throw new RuntimeException('Adyen configuration is incomplete.');
    }

    $url = rtrim(roboforge_adyen_checkout_base(), '/') . '/' . ltrim($endpoint, '/');
    $ch = curl_init($url);
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . ADYEN_API_KEY,
        ],
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Adyen request failed: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Unexpected Adyen response: ' . $response);
    }

    if ($status < 200 || $status >= 300) {
        $message = $decoded['message'] ?? ('HTTP ' . $status);
        throw new RuntimeException('Adyen API error: ' . $message);
    }

    return $decoded;
}

function roboforge_adyen_amount_minor(float $amount): int
{
    return (int) round($amount * 100);
}

function roboforge_adyen_get_or_create_shopper(PDO $pdo, int $userId, string $email, ?string $country = null): string
{
    $select = $pdo->prepare('SELECT shopper_reference, email, billing_country FROM adyen_shoppers WHERE user_id = ? LIMIT 1');
    $select->execute([$userId]);
    $existing = $select->fetch();

    $countryCode = null;
    if ($country) {
        $countryCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $country), 0, 2));
    }

    if ($existing) {
        if ($existing['email'] !== $email || ($countryCode && $countryCode !== ($existing['billing_country'] ?? null))) {
            $update = $pdo->prepare('UPDATE adyen_shoppers SET email = ?, billing_country = ?, updated_at = NOW() WHERE user_id = ?');
            $update->execute([$email, $countryCode, $userId]);
        }
        return $existing['shopper_reference'];
    }

    $reference = 'user-' . $userId;
    $insert = $pdo->prepare('INSERT INTO adyen_shoppers (user_id, shopper_reference, email, billing_country) VALUES (?, ?, ?, ?)');
    $insert->execute([$userId, $reference, $email, $countryCode]);

    return $reference;
}

function roboforge_adyen_store_token(array $additionalData, string $shopperReference): ?string
{
    $detailReference = $additionalData['recurring.recurringDetailReference'] ?? null;
    $storedPaymentMethodId = $additionalData['storedPaymentMethodId'] ?? ($additionalData['recurring.shopperReference'] ?? null);
    if (!$detailReference) {
        return null;
    }

    $pdo = roboforge_db();
    $id = roboforge_uuid();
    $brand = $additionalData['paymentMethod'] ?? null;
    $cardLast4 = $additionalData['cardSummary'] ?? null;
    $expiryMonth = $additionalData['expiryDate'] ?? null;
    $expiryYear = null;
    if ($expiryMonth && strpos($expiryMonth, '/') !== false) {
        [$month, $year] = array_map('trim', explode('/', $expiryMonth));
        $expiryMonth = $month;
        $expiryYear = $year;
    }

    $model = $additionalData['recurringProcessingModel'] ?? 'CardOnFile';

    $stmt = $pdo->prepare('INSERT INTO payment_tokens (id, shopper_reference, recurring_detail_reference, stored_payment_method_id, brand, card_last4, expiry_month, expiry_year, recurring_processing_model)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE brand = VALUES(brand), card_last4 = VALUES(card_last4), expiry_month = VALUES(expiry_month), expiry_year = VALUES(expiry_year), recurring_processing_model = VALUES(recurring_processing_model), updated_at = NOW()');
    $stmt->execute([
        $id,
        $shopperReference,
        $detailReference,
        $storedPaymentMethodId,
        $brand,
        $cardLast4,
        $expiryMonth,
        $expiryYear,
        $model,
    ]);

    return $detailReference;
}

function roboforge_adyen_verify_hmac(array $notification): bool
{
    if (ADYEN_HMAC_KEY === '') {
        return false;
    }

    $items = $notification['NotificationRequestItem'] ?? null;
    if (!$items) {
        return false;
    }

    $fields = [
        'pspReference',
        'originalReference',
        'merchantAccountCode',
        'merchantReference',
        'amount.value',
        'amount.currency',
        'eventCode',
        'success',
    ];

    $data = [];
    foreach ($fields as $field) {
        if (strpos($field, '.') !== false) {
            [$parent, $child] = explode('.', $field, 2);
            $data[] = $items[$parent][$child] ?? '';
        } else {
            $data[] = $items[$field] ?? '';
        }
    }

    $signingData = implode(':', $data);
    $expected = $items['additionalData']['hmacSignature'] ?? '';
    if ($expected === '') {
        return false;
    }

    $calculated = base64_encode(hash_hmac('sha256', $signingData, base64_decode(ADYEN_HMAC_KEY), true));
    return hash_equals($expected, $calculated);
}
