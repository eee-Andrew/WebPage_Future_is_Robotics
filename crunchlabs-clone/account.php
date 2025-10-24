<?php
$pageTitle = 'Your saved items';
$showHero = false;
$bodyClass = 'account-page';
require_once __DIR__ . '/db.php';
crunchlabs_require_login();

$currentUser = crunchlabs_current_user();
$redirect = $_POST['redirect'] ?? ($_GET['redirect'] ?? crunchlabs_url('account.php'));
$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    if ($productId <= 0) {
        crunchlabs_flash_set('error', 'Select a valid product.');
        crunchlabs_redirect($redirect);
    }

    if ($action === 'save') {
        $insert = $pdo->prepare('INSERT IGNORE INTO saved_items (user_id, product_id) VALUES (?, ?)');
        $insert->execute([$currentUser['id'], $productId]);
        crunchlabs_flash_set('success', 'Product saved to your account.');
    } elseif ($action === 'remove') {
        $delete = $pdo->prepare('DELETE FROM saved_items WHERE user_id = ? AND product_id = ?');
        $delete->execute([$currentUser['id'], $productId]);
        crunchlabs_flash_set('success', 'Product removed from saved items.');
    }

    crunchlabs_redirect($redirect);
}

$saved = $pdo->prepare('SELECT p.*, s.saved_at FROM saved_items s JOIN products p ON p.id = s.product_id WHERE s.user_id = ? ORDER BY s.saved_at DESC');
$saved->execute([$currentUser['id']]);
$savedItems = $saved->fetchAll();

require_once __DIR__ . '/partials/header.php';
?>
<section class="account-header">
    <h2 data-i18n="account.title">Saved items</h2>
    <p data-i18n="account.subtitle">Revisit kits you have set aside and add them to your cart when you are ready.</p>
</section>

<section class="saved-items">
    <?php if (!$savedItems): ?>
        <p class="empty-state" data-i18n="account.empty">You have not saved any products yet.</p>
    <?php else: ?>
        <div class="saved-grid">
            <?php foreach ($savedItems as $item): ?>
                <?php $imagePath = crunchlabs_public_path($item['image_path'], crunchlabs_url('assets/img/products/placeholder.svg')); ?>
                <article class="saved-card">
                    <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="saved-info">
                        <h3><?= htmlspecialchars($item['name']); ?></h3>
                        <p><?= htmlspecialchars($item['short_description']); ?></p>
                        <span class="price">$<?= number_format((float) $item['price'], 2); ?></span>
                        <div class="saved-actions">
                            <form method="post" action="<?= htmlspecialchars(crunchlabs_url('cart.php')); ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="redirect" value="<?= htmlspecialchars(crunchlabs_url('account.php')); ?>">
                                <button type="submit" class="cta-button" data-i18n="account.addCart">Add to cart</button>
                            </form>
                            <form method="post" action="<?= htmlspecialchars(crunchlabs_url('account.php')); ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id']; ?>">
                                <input type="hidden" name="redirect" value="<?= htmlspecialchars(crunchlabs_url('account.php')); ?>">
                                <button type="submit" class="btn-danger" data-i18n="account.remove">Remove</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
