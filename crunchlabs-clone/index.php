<?php
$pageTitle = 'CrunchLabs Inspired Landing';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/partials/header.php';

$stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 6');
$products = $stmt->fetchAll();
?>
<section class="features">
    <h2>How It Works</h2>
    <div class="feature-grid">
        <div class="feature">
            <h3>Build</h3>
            <p>Hands-on kits designed by engineers.</p>
        </div>
        <div class="feature">
            <h3>Learn</h3>
            <p>Video lessons and experiments each month.</p>
        </div>
        <div class="feature">
            <h3>Compete</h3>
            <p>Join the community and share creations.</p>
        </div>
    </div>
</section>

<section class="products" id="products">
    <h2>Latest Kits</h2>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <img src="<?= htmlspecialchars($product['image_path'] ?? '/crunchlabs-clone/assets/img/placeholder.jpg'); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                <h3><?= htmlspecialchars($product['name']); ?></h3>
                <p><?= htmlspecialchars($product['description']); ?></p>
                <div class="product-meta">
                    <span class="price">$<?= number_format($product['price'], 2); ?></span>
                    <span class="qty"><?= (int)$product['quantity']; ?> in stock</span>
                </div>
                <button class="btn-secondary">Learn More</button>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="testimonials">
    <h2>What Builders Say</h2>
    <div class="testimonial-grid">
        <blockquote>
            “Every month brings a new challenge that sparks creativity in our family!”
            <cite>— Jamie R.</cite>
        </blockquote>
        <blockquote>
            “My students love the kits—engaging, educational, and fun.”
            <cite>— Mr. Carter</cite>
        </blockquote>
    </div>
</section>

<section class="cta">
    <h2>Ready to Start Building?</h2>
    <p>Join thousands of makers discovering the future of robotics.</p>
    <a class="cta-button" href="/crunchlabs-clone/register.php">Join Now</a>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
