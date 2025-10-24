<?php
$catalogHeading = $catalogHeading ?? 'Explore our prototypes';
$catalogSubheading = $catalogSubheading ?? 'Select a category below to open detailed layers for each kit, add them to your cart, or save them for later.';
$catalogHeadingKey = $catalogHeadingKey ?? 'catalog.title';
$catalogSubheadingKey = $catalogSubheadingKey ?? 'catalog.subtitle';
$currentUrl = $currentUrl ?? ($_SERVER['REQUEST_URI'] ?? roboforge_url('home.php'));
?>
<section class="catalog-intro" id="products">
    <h2 data-i18n="<?= htmlspecialchars($catalogHeadingKey); ?>"><?= htmlspecialchars($catalogHeading); ?></h2>
    <p data-i18n="<?= htmlspecialchars($catalogSubheadingKey); ?>"><?= htmlspecialchars($catalogSubheading); ?></p>
</section>

<?php foreach ($categoryMeta as $slug => $meta): ?>
    <section class="product-category" id="<?= htmlspecialchars($meta['anchor']); ?>">
        <div class="category-header">
            <h3 data-i18n="category.<?= htmlspecialchars($slug); ?>.title"><?= htmlspecialchars($meta['title']); ?></h3>
            <p data-i18n="category.<?= htmlspecialchars($slug); ?>.description"><?= htmlspecialchars($meta['description']); ?></p>
        </div>
        <div class="product-grid">
            <?php foreach ($groupedProducts[$slug] ?? [] as $product): ?>
                <?php
                    $imagePath = roboforge_public_path($product['image_path'], $fallbackImage);
                    $longDesc = $product['long_description'] ?: $product['short_description'];
                ?>
                <article
                    class="product-card"
                    tabindex="0"
                    data-product-id="<?= (int) $product['id']; ?>"
                    data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES); ?>"
                    data-product-short="<?= htmlspecialchars($product['short_description'], ENT_QUOTES); ?>"
                    data-product-long="<?= htmlspecialchars($longDesc, ENT_QUOTES); ?>"
                    data-product-price="<?= number_format((float) $product['price'], 2, '.', ''); ?>"
                    data-product-image="<?= htmlspecialchars($imagePath, ENT_QUOTES); ?>"
                >
                    <figure>
                        <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                        <figcaption class="product-caption">ID #<?= (int) $product['id']; ?> — <?= htmlspecialchars($product['name']); ?></figcaption>
                    </figure>
                    <p><?= htmlspecialchars($product['short_description']); ?></p>
                    <div class="product-meta">
                        <span class="price">$<?= number_format((float) $product['price'], 2); ?></span>
                        <span class="qty" data-i18n="product.inStock" data-i18n-params='{"count":<?= (int) $product['quantity']; ?>}'><?= (int) $product['quantity']; ?> in stock</span>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (empty($groupedProducts[$slug])): ?>
                <p class="empty-state" data-i18n="product.empty">No products available in this category yet.</p>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>

<div class="product-overlay hidden" id="product-overlay" aria-hidden="true">
    <div class="overlay-backdrop" id="overlay-backdrop"></div>
    <div class="overlay-panel" role="dialog" aria-modal="true" aria-labelledby="overlay-title">
        <button class="overlay-close" type="button" id="overlay-close" aria-label="Close">&times;</button>
        <div class="overlay-content">
            <div class="overlay-image">
                <img src="<?= htmlspecialchars($fallbackImage); ?>" alt="" id="overlay-image">
            </div>
            <div class="overlay-details">
                <h2 id="overlay-title"></h2>
                <p class="overlay-short" id="overlay-short"></p>
                <div class="overlay-price" id="overlay-price"></div>
                <?php if (roboforge_is_logged_in()): ?>
                    <form method="post" action="<?= htmlspecialchars(roboforge_url('cart.php')); ?>" class="overlay-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" id="overlay-product-id-cart">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($currentUrl); ?>">
                        <label data-i18n="overlay.quantity">Quantity
                            <input type="number" name="quantity" id="overlay-quantity" min="1" value="1" required>
                        </label>
                        <button type="submit" class="cta-button" data-i18n="overlay.addCart">Add to cart</button>
                    </form>
                    <form method="post" action="<?= htmlspecialchars(roboforge_url('account.php')); ?>" class="overlay-form secondary">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="product_id" id="overlay-product-id-save">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($currentUrl); ?>">
                        <button type="submit" class="btn-secondary" data-i18n="overlay.save">Save to account</button>
                    </form>
                <?php else: ?>
                    <div class="overlay-auth-callout">
                        <p data-i18n="overlay.loginPrompt">Log in to add this kit to your cart or save it for later.</p>
                        <a class="cta-button" href="<?= htmlspecialchars(roboforge_url('login.php')); ?>" data-i18n="overlay.login">Log in</a>
                    </div>
                <?php endif; ?>
                <div class="overlay-long" id="overlay-long"></div>
            </div>
        </div>
    </div>
</div>
