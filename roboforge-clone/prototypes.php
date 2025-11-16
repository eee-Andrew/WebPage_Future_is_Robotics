<?php
$pageTitle = 'RoboForge Prototypes';
$showHero = false;
$bodyClass = 'catalog-page';
require_once __DIR__ . '/db.php';

$productQuery = $pdo->query('SELECT * FROM products ORDER BY category_slug, name ASC');
$products = $productQuery->fetchAll();
$fallbackImage = roboforge_url('assets/img/products/placeholder.svg');

$groupedProducts = [];
foreach ($products as $product) {
    $groupedProducts[$product['category_slug']][] = $product;
}

$categoryMeta = [
    'preschool' => [
        'title' => 'Preschool prototypes',
        'description' => 'Hands-on play sets that introduce motion, colour, and sequencing to early learners.',
        'anchor' => 'prototype-preschool',
    ],
    'primary' => [
        'title' => 'Primary school innovators',
        'description' => 'Story-driven builds that teach core robotics concepts through guided missions.',
        'anchor' => 'prototype-primary',
    ],
    'highschool' => [
        'title' => 'High school innovators',
        'description' => 'Project-ready kits that develop experimentation, teamwork, and engineering judgement.',
        'anchor' => 'prototype-highschool',
    ],
    'university' => [
        'title' => 'University research labs',
        'description' => 'Advanced platforms designed for autonomy, control systems, and mission rehearsal.',
        'anchor' => 'prototype-university',
    ],
];

$currentUrl = $_SERVER['REQUEST_URI'] ?? roboforge_url('prototypes.php');
$catalogHeading = 'Compare every RoboForge prototype';
$catalogSubheading = 'Dive into each learning stage, open the overlay for specs, and add the kits you need directly to your cart.';
$catalogHeadingKey = 'catalog.compareTitle';
$catalogSubheadingKey = 'catalog.compareSubtitle';

require_once __DIR__ . '/partials/header.php';
?>
<section class="info-section" id="prototype-overview">
    <h2>Prototyping pathways</h2>
    <p>
        Each RoboForge pathway contains curated builds aligned to specific learning outcomes. Browse the stages below or use the
        navigation menu to jump straight to the level that matches your classroom or lab.
    </p>
</section>

<?php require __DIR__ . '/partials/catalog.php'; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
