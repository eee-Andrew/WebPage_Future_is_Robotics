<?php
if (!isset($pageTitle)) {
    $pageTitle = 'RoboForge';
}

$showHero = $showHero ?? true;
$bodyClass = isset($bodyClass) ? trim((string) $bodyClass) : '';
$currentUser = roboforge_current_user();
$cartCount = 0;

if ($currentUser && isset($pdo) && $pdo instanceof PDO) {
    $cartStmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) AS total FROM cart_items WHERE user_id = ?');
    $cartStmt->execute([$currentUser['id']]);
    $cartCount = (int) $cartStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(roboforge_url('assets/css/styles.css')); ?>">
</head>
<body class="<?= htmlspecialchars($bodyClass); ?>">
<header class="site-header">
    <nav class="navbar">
        <a class="logo" href="<?= htmlspecialchars(roboforge_url('index.php')); ?>">RoboForge</a>
        <ul class="nav-sections">
            <li class="nav-item has-dropdown">
                <div class="nav-item-header">
                    <a class="nav-link" href="<?= htmlspecialchars(roboforge_url('home.php')); ?>" data-i18n="nav.home">Home</a>
                    <button class="nav-trigger" type="button" data-dropdown="home" aria-label="Toggle home menu" aria-expanded="false"></button>
                </div>
                <div class="dropdown" id="dropdown-home">
                    <a href="<?= htmlspecialchars(roboforge_url('home.php#who-we-are')); ?>" data-i18n="nav.who">Who we are</a>
                    <a href="<?= htmlspecialchars(roboforge_url('home.php#why-products')); ?>" data-i18n="nav.why">Why these products</a>
                    <a href="<?= htmlspecialchars(roboforge_url('home.php#future-robotics')); ?>" data-i18n="nav.future">Future is Robotics</a>
                </div>
            </li>
            <li class="nav-item has-dropdown">
                <div class="nav-item-header">
                    <a class="nav-link" href="<?= htmlspecialchars(roboforge_url('prototypes.php')); ?>" data-i18n="nav.prototypes">Prototypes</a>
                    <button class="nav-trigger" type="button" data-dropdown="prototypes" aria-label="Toggle prototypes menu" aria-expanded="false"></button>
                </div>
                <div class="dropdown" id="dropdown-prototypes">
                    <a href="<?= htmlspecialchars(roboforge_url('prototypes.php#prototype-preschool')); ?>" data-i18n="nav.preschool">Preschool</a>
                    <a href="<?= htmlspecialchars(roboforge_url('prototypes.php#prototype-primary')); ?>" data-i18n="nav.primary">Primary</a>
                    <a href="<?= htmlspecialchars(roboforge_url('prototypes.php#prototype-highschool')); ?>" data-i18n="nav.highschool">High School</a>
                    <a href="<?= htmlspecialchars(roboforge_url('prototypes.php#prototype-university')); ?>" data-i18n="nav.university">University</a>
                </div>
            </li>
            <li class="nav-item has-dropdown">
                <div class="nav-item-header">
                    <a class="nav-link" href="<?= htmlspecialchars(roboforge_url('resources.php')); ?>" data-i18n="nav.resources">Resources</a>
                    <button class="nav-trigger" type="button" data-dropdown="resources" aria-label="Toggle resources menu" aria-expanded="false"></button>
                </div>
                <div class="dropdown" id="dropdown-resources">
                    <a href="<?= htmlspecialchars(roboforge_url('account.php')); ?>" data-i18n="nav.saved">Saved items</a>
                    <a href="<?= htmlspecialchars(roboforge_url('cart.php')); ?>" data-i18n="nav.cart">Cart</a>
                    <a href="<?= htmlspecialchars(roboforge_url('resources.php#contact')); ?>" data-i18n="nav.contact">Contact us</a>
                </div>
            </li>
        </ul>
        <div class="nav-actions">
            <button class="lang-toggle" type="button" id="language-toggle" aria-label="Toggle language">ΕΛ / EN</button>
            <a class="icon-button" href="<?= htmlspecialchars(roboforge_url(roboforge_is_logged_in() ? 'account.php' : 'login.php')); ?>" aria-label="Account">
                <span class="icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.76 0 5-2.69 5-6s-2.24-6-5-6-5 2.69-5 6 2.24 6 5 6zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/></svg>
                </span>
                <span class="icon-label" data-i18n="nav.account">Account</span>
            </a>
            <a class="icon-button cart-button" href="<?= htmlspecialchars(roboforge_url('cart.php')); ?>" aria-label="Cart">
                <span class="icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h-2l-1 2v2h2l3.6 7.59-1.35 2.44c-.16.28-.25.61-.25.97 0 1.1.9 2 2 2h12v-2h-11.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.59h7.54c.75 0 1.41-.42 1.75-1.09l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1h-16.31l-.94-2zm3 18c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm10 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                </span>
                <span class="icon-label" data-i18n="nav.cartShort">Cart</span>
                <?php if ($cartCount > 0): ?>
                    <span class="badge"><?= $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <?php if (roboforge_is_logged_in()): ?>
                <form method="post" action="<?= htmlspecialchars(roboforge_url('logout.php')); ?>" class="logout-form">
                    <button type="submit" class="logout-button" data-i18n="nav.logout">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </nav>
</header>
<?php if ($showHero): ?>
    <section class="hero">
        <div class="hero-inner">
            <h1 data-i18n="hero.title">Forge your future in robotics</h1>
            <p data-i18n="hero.subtitle">Interactive kits, challenges, and community—all in one place.</p>
            <a class="cta-button" href="<?= htmlspecialchars(roboforge_url('prototypes.php#prototype-preschool')); ?>" data-i18n="hero.cta">Explore kits</a>
        </div>
    </section>
<?php endif; ?>
<main class="<?= $showHero ? '' : 'main-plain'; ?>">
    <?php if ($message = roboforge_flash_get('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($message = roboforge_flash_get('error')): ?>
        <div class="alert alert-error"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
