<?php
if (!isset($pageTitle)) {
    $pageTitle = 'CrunchLabs Inspired';
}

$showHero = $showHero ?? true;
$bodyClass = isset($bodyClass) ? trim((string) $bodyClass) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(crunchlabs_url('assets/css/styles.css')); ?>">
</head>
<body class="<?= htmlspecialchars($bodyClass); ?>">
<header class="<?= $showHero ? 'hero' : 'site-header'; ?>">
    <nav class="navbar">
        <div class="logo">CrunchLabs</div>
        <ul class="nav-links">
            <li><a href="<?= htmlspecialchars(crunchlabs_url('index.php')); ?>">Home</a></li>
            <li><a href="<?= htmlspecialchars(crunchlabs_url('index.php')); ?>">Products</a></li>
            <?php if (!empty($_SESSION['user'])): ?>
                <li><a href="<?= htmlspecialchars(crunchlabs_url('logout.php')); ?>">Logout</a></li>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="<?= htmlspecialchars(crunchlabs_url('admin/dashboard.php')); ?>">Admin</a></li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="<?= htmlspecialchars(crunchlabs_url('login.php')); ?>">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php if ($showHero): ?>
        <div class="hero-content">
            <h1>Future of Robotics Starts Here</h1>
            <p>Interactive kits, challenges, and community—all in one place.</p>
            <a class="cta-button" href="<?= htmlspecialchars(crunchlabs_url('index.php')); ?>">Browse Products</a>
        </div>
    <?php endif; ?>
</header>
<main class="<?= $showHero ? '' : 'main-plain'; ?>">
