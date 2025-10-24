<?php
$pageTitle = 'Your cart';
$showHero = false;
$bodyClass = 'cart-page';
require_once __DIR__ . '/db.php';
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

    if ($action === 'checkout') {
        $requiredFields = [
            'full_name', 'address_line1', 'city', 'postal_code', 'country',
            'email', 'phone', 'card_number', 'card_expiry', 'card_cvv'
        ];

        $errors = [];
        foreach ($requiredFields as $field) {
            if (trim((string)($_POST[$field] ?? '')) === '') {
                $errors[] = 'Please complete all required fields.';
                break;
            }
        }

        $cartItemsStmt = $pdo->prepare('SELECT c.product_id, c.quantity, p.price FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
        $cartItemsStmt->execute([$currentUser['id']]);
        $cartItems = $cartItemsStmt->fetchAll();

        if (!$cartItems) {
            $errors[] = 'Your cart is empty.';
        }

        if (!$errors) {
            $total = 0;
            foreach ($cartItems as $item) {
                $total += (float) $item['price'] * (int) $item['quantity'];
            }

            $pdo->beginTransaction();
            try {
                $insertOrder = $pdo->prepare('INSERT INTO orders (user_id, total, full_name, address_line1, address_line2, city, postal_code, country, email, phone, card_last4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $cardNumber = preg_replace('/\D+/', '', (string) $_POST['card_number']);
                $last4 = substr($cardNumber, -4) ?: '0000';
                $insertOrder->execute([
                    $currentUser['id'],
                    $total,
                    trim((string) $_POST['full_name']),
                    trim((string) $_POST['address_line1']),
                    trim((string) ($_POST['address_line2'] ?? '')),
                    trim((string) $_POST['city']),
                    trim((string) $_POST['postal_code']),
                    trim((string) $_POST['country']),
                    trim((string) $_POST['email']),
                    trim((string) $_POST['phone']),
                    $last4,
                ]);
                $orderId = (int) $pdo->lastInsertId();

                $insertItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_each) VALUES (?, ?, ?, ?)');
                foreach ($cartItems as $item) {
                    $insertItem->execute([
                        $orderId,
                        (int) $item['product_id'],
                        (int) $item['quantity'],
                        (float) $item['price'],
                    ]);
                }

                $clearCart = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
                $clearCart->execute([$currentUser['id']]);

                $pdo->commit();
                roboforge_flash_set('success', 'Thank you! Your order has been placed.');
            } catch (Throwable $e) {
                $pdo->rollBack();
                roboforge_flash_set('error', 'We could not complete your checkout. Please try again.');
            }
        } else {
            roboforge_flash_set('error', $errors[0]);
        }

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

require_once __DIR__ . '/partials/header.php';
?>
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
                <form method="post" action="<?= htmlspecialchars(roboforge_url('cart.php')); ?>" class="form-card">
                    <input type="hidden" name="action" value="checkout">
                    <label>
                        <span class="form-label" data-i18n="checkout.name">Full name</span>
                        <input type="text" name="full_name" required>
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.address1">Address line 1</span>
                        <input type="text" name="address_line1" required>
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.address2">Address line 2</span>
                        <input type="text" name="address_line2">
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.city">City</span>
                        <input type="text" name="city" required>
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.postal">Postal code</span>
                        <input type="text" name="postal_code" required>
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.country">Country</span>
                        <input type="text" name="country" required>
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.email">Email</span>
                        <input type="email" name="email" required>
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.phone">Phone</span>
                        <input type="tel" name="phone" required>
                    </label>
                    <label>
                        <span class="form-label" data-i18n="checkout.card">Card number</span>
                        <input type="text" name="card_number" required>
                    </label>
                    <div class="card-row">
                        <label>
                            <span class="form-label" data-i18n="checkout.expiry">Expiry (MM/YY)</span>
                            <input type="text" name="card_expiry" required>
                        </label>
                        <label>
                            <span class="form-label" data-i18n="checkout.cvv">CVV</span>
                            <input type="text" name="card_cvv" required>
                        </label>
                    </div>
                    <button type="submit" class="cta-button" data-i18n="checkout.submit">Pay now</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
