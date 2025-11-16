<?php
$pageTitle = 'Your cart';
$showHero = false;
$bodyClass = 'cart-page';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/services/adyen.php';
roboforge_require_login();

$currentUser = roboforge_current_user();
$action = $_POST['action'] ?? null;
$redirectBack = $_POST['redirect'] ?? roboforge_url('cart.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $product = $pdo->prepare('SELECT id FROM products WHERE id = ?');
        $product->execute([$productId]);
        if ($product->fetch()) {
            $add = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
            $add->execute([$currentUser['id'], $productId, $quantity]);
            roboforge_flash_set('success', 'Product added to your cart.');
        } else {
            roboforge_flash_set('error', 'Unable to add the selected product.');
        }
        roboforge_redirect($redirectBack);
    }

    if ($action === 'update') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        if ($quantity <= 0) {
            $delete = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
            $delete->execute([$currentUser['id'], $productId]);
            roboforge_flash_set('success', 'Item removed from your cart.');
        } else {
            $update = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?');
            $update->execute([$quantity, $currentUser['id'], $productId]);
            roboforge_flash_set('success', 'Cart updated.');
        }
        roboforge_redirect('cart.php');
    }

    if ($action === 'remove') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $delete = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
        $delete->execute([$currentUser['id'], $productId]);
        roboforge_flash_set('success', 'Item removed from your cart.');
        roboforge_redirect('cart.php');
    }

}

$itemsStmt = $pdo->prepare('SELECT c.quantity, p.* FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.user_id = ? ORDER BY p.name ASC');
$itemsStmt->execute([$currentUser['id']]);
$cartItems = $itemsStmt->fetchAll();

$totalCost = 0;
foreach ($cartItems as $item) {
    $totalCost += (float) $item['price'] * (int) $item['quantity'];
}

$extraHead = [];
if (roboforge_adyen_is_configured()) {
    $sdkBase = roboforge_adyen_sdk_base();
    $extraHead[] = '<link rel="stylesheet" href="' . htmlspecialchars($sdkBase . '/adyen.css', ENT_QUOTES, 'UTF-8') . '">';
    $extraHead[] = '<script defer src="' . htmlspecialchars($sdkBase . '/adyen.js', ENT_QUOTES, 'UTF-8') . '"></script>';
}

$paymentConfig = [
    'configUrl' => roboforge_url('payments/config.php'),
    'createPaymentUrl' => roboforge_url('payments/create.php'),
    'detailsUrl' => roboforge_url('payments/details.php'),
    'currency' => ROBOFORGE_CURRENCY,
];

require_once __DIR__ . '/partials/header.php';
?>
<script>
window.roboforgePaymentConfig = <?= json_encode($paymentConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>
<section class="cart-section">
    <h2 data-i18n="cart.title">Your cart</h2>
    <?php if (!$cartItems): ?>
        <p class="empty-state" data-i18n="cart.empty">Your cart is currently empty.</p>
    <?php else: ?>
        <div class="cart-grid">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <?php $imagePath = roboforge_public_path($item['image_path'], roboforge_url('assets/img/products/placeholder.svg')); ?>
                    <article class="cart-card">
                        <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                        <div class="cart-info">
                            <h3><?= htmlspecialchars($item['name']); ?></h3>
                            <p><?= htmlspecialchars($item['short_description']); ?></p>
                            <div class="cart-meta">
                                <span class="price">$<?= number_format((float) $item['price'], 2); ?></span>
                                <form method="post" action="<?= htmlspecialchars(roboforge_url('cart.php')); ?>" class="quantity-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['id']; ?>">
                                    <label>
                                        <span class="form-label" data-i18n="cart.quantity">Qty</span>
                                        <input type="number" name="quantity" min="0" value="<?= (int) $item['quantity']; ?>">
                                    </label>
                                    <button type="submit" class="btn-secondary" data-i18n="cart.update">Update</button>
                                </form>
                            </div>
                            <form method="post" action="<?= htmlspecialchars(roboforge_url('cart.php')); ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id']; ?>">
                                <button type="submit" class="btn-danger" data-i18n="cart.remove">Remove</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
                <div class="cart-total">
                    <span data-i18n="cart.total">Total</span>
                    <strong>$<?= number_format($totalCost, 2); ?></strong>
                </div>
            </div>
            <div class="checkout-form">
                <h3 data-i18n="checkout.title">Checkout</h3>
                <form id="checkout-form" class="form-card" novalidate>
                    <div class="form-field">
                        <label for="checkout-full-name">
                            <span class="form-label" data-i18n="checkout.name">Full name</span>
                            <input type="text" id="checkout-full-name" name="full_name" data-checkout-field required>
                        </label>
                    </div>
                    <div class="form-field">
                        <label for="checkout-address1">
                            <span class="form-label" data-i18n="checkout.address1">Address line 1</span>
                            <input type="text" id="checkout-address1" name="address_line1" data-checkout-field required>
                        </label>
                    </div>
                    <div class="form-field">
                        <label for="checkout-address2">
                            <span class="form-label" data-i18n="checkout.address2">Address line 2</span>
                            <input type="text" id="checkout-address2" name="address_line2" data-checkout-field>
                        </label>
                    </div>
                    <div class="form-row">
                        <label for="checkout-city">
                            <span class="form-label" data-i18n="checkout.city">City</span>
                            <input type="text" id="checkout-city" name="city" data-checkout-field required>
                        </label>
                        <label for="checkout-postal">
                            <span class="form-label" data-i18n="checkout.postal">Postal code</span>
                            <input type="text" id="checkout-postal" name="postal_code" data-checkout-field required>
                        </label>
                    </div>
                    <div class="form-field">
                        <label for="checkout-country">
                            <span class="form-label" data-i18n="checkout.country">Country (ISO code preferred)</span>
                            <input type="text" id="checkout-country" name="country" data-checkout-field required>
                        </label>
                    </div>
                    <div class="form-field">
                        <label for="checkout-email">
                            <span class="form-label" data-i18n="checkout.email">Email</span>
                            <input type="email" id="checkout-email" name="email" data-checkout-field required>
                        </label>
                    </div>
                    <div class="form-field">
                        <label for="checkout-phone">
                            <span class="form-label" data-i18n="checkout.phone">Phone</span>
                            <input type="tel" id="checkout-phone" name="phone" data-checkout-field required>
                        </label>
                    </div>
                    <div class="form-field payment-component">
                        <span class="form-label" data-i18n="checkout.paymentTitle">Secure payment</span>
                        <div id="adyen-dropin" class="adyen-dropin"></div>
                    </div>
                    <p class="checkout-note" data-i18n="checkout.secureNote">Card details are encrypted and handled directly by Adyen to keep RoboForge out of PCI scope.</p>
                    <div id="payment-messages" role="status" aria-live="polite"></div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
