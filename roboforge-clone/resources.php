<?php
$pageTitle = 'RoboForge Resources';
$showHero = false;
$bodyClass = 'resources-page';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/partials/header.php';
?>
<section class="info-section" id="resources-intro">
    <h2>Your RoboForge workspace</h2>
    <p>
        Keep track of saved kits, monitor your cart, and reach out to the RoboForge team with the links below. These sections work
        the same way whether you browse on the home page or jump straight here from the navigation menu.
    </p>
</section>

<section class="info-section" id="saved-items">
    <h3>Saved items</h3>
    <p>
        Use the <strong>Account</strong> icon to log in and visit your saved items page. Every kit you mark for later appears in a
        personal list with quick buttons to add it to your cart or remove it entirely. Saved items stay with your account across
        sessions, so you can plan builds in advance.
    </p>
    <p>
        <a class="cta-button" href="<?= htmlspecialchars(roboforge_url('account.php')); ?>">Open my saved items</a>
    </p>
</section>

<section class="info-section" id="cart-tools">
    <h3>Cart and checkout</h3>
    <p>
        When you add products to your cart, you can adjust quantities directly from the cart page. The checkout form captures the
        shipping address, contact information, and payment details required to dispatch your RoboForge kits.
    </p>
    <p>
        <a class="btn-secondary" href="<?= htmlspecialchars(roboforge_url('cart.php')); ?>">Review my cart</a>
    </p>
</section>

<section class="info-section" id="contact">
    <h3>Need assistance?</h3>
    <p>
        Reach us at <a href="mailto:support@roboforge.local">support@roboforge.local</a> or call <a href="tel:+1234567890">+1 (234)
        567-890</a> for help with orders, product recommendations, or classroom planning.
    </p>
    <p>
        Our team responds to email within one business day. Phone support is available Monday–Friday, 9am–5pm local time.
    </p>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
