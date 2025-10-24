<?php
$pageTitle = 'Robotics Kits Catalog';
$showHero = false;
$bodyClass = 'catalog-page';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/partials/header.php';

$stmt = $pdo->query('SELECT * FROM products ORDER BY name ASC');
$products = $stmt->fetchAll();
$fallbackImage = crunchlabs_url('assets/img/products/placeholder.svg');
?>
<section class="catalog-header">
    <h1>Browse Our Robotics Kits</h1>
    <p>Every product below has a unique ID in the database so you can keep inventory aligned with the correct photo and caption.</p>
</section>

<section class="products" id="products">
    <?php if (!$products): ?>
        <p class="empty-state">No products are available yet. Sign in as an admin to add your first kit.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php
                    $imagePath = crunchlabs_public_path($product['image_path'], $fallbackImage);
                    $description = $product['description'] ?: 'Stay tuned for more details on this build!';
                ?>
                <article class="product-card" data-product-id="<?= (int) $product['id']; ?>">
                    <figure>
                        <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                        <figcaption class="product-caption">ID #<?= (int) $product['id']; ?> — <?= htmlspecialchars($product['name']); ?></figcaption>
                    </figure>
                    <p><?= htmlspecialchars($description); ?></p>
                    <div class="product-meta">
                        <span class="price">$<?= number_format((float) $product['price'], 2); ?></span>
                        <span class="qty"><?= (int) $product['quantity']; ?> in stock</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
