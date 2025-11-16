<?php
$pageTitle = 'Robotics Kits Catalog';
$showHero = true;
$bodyClass = 'catalog-page';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/partials/header.php';

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
        'description' => 'Gentle builds that introduce motion, color, and cause-and-effect for the youngest makers.',
        'anchor' => 'prototype-preschool',
    ],
    'primary' => [
        'title' => 'Primary school innovators',
        'description' => 'Curated challenges that blend storytelling with hands-on robotics for growing explorers.',
        'anchor' => 'prototype-primary',
    ],
    'highschool' => [
        'title' => 'High school innovators',
        'description' => 'Rigorous builds that sharpen engineering judgment, teamwork, and experimentation.',
        'anchor' => 'prototype-highschool',
    ],
    'university' => [
        'title' => 'University research labs',
        'description' => 'Advanced systems that dive into autonomy, feedback loops, and mission readiness.',
        'anchor' => 'prototype-university',
    ],
];

$currentUrl = $_SERVER['REQUEST_URI'] ?? roboforge_url('home.php');
?>
<section id="who-we-are" class="info-section">
    <h2 data-i18n="who.title">Who we are</h2>
    <p data-i18n="who.text">We are a collective of educators, engineers, and designers delivering robotics journeys for every age. Our studios build real-world challenges that help learners imagine, prototype, and launch ideas that matter.</p>
</section>

<section id="why-products" class="info-section">
    <h2 data-i18n="why.title">Why these products</h2>
    <p data-i18n="why.text">Each kit is engineered with modular parts, video guidance, and classroom-ready lesson paths. Families, schools, and makerspaces can expand or customise the builds without starting from scratch.</p>
</section>

<section id="future-robotics" class="info-section">
    <h2 data-i18n="future.title">Future is Robotics</h2>
    <p data-i18n="future.text">Robotics unlocks creative confidence, problem solving, and collaboration. Our community shares monthly missions, live workshops, and research briefs so every builder can keep learning.</p>
</section>

<?php require __DIR__ . '/partials/catalog.php'; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
