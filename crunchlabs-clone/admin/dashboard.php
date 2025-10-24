<?php
require_once __DIR__ . '/../db.php';

$totalProducts = $pdo->query('SELECT COUNT(*) AS cnt FROM products')->fetch()['cnt'];
$lowStock = $pdo->query('SELECT COUNT(*) AS cnt FROM products WHERE quantity < 5')->fetch()['cnt'];
$showHero = false;
$bodyClass = 'admin-page';
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../partials/header.php';
?>
<section class="admin-dashboard">
    <h2>Admin Overview</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Products</h3>
            <p><?= (int)$totalProducts; ?></p>
        </div>
        <div class="stat-card">
            <h3>Low Inventory</h3>
            <p><?= (int)$lowStock; ?></p>
        </div>
    </div>
    <a class="btn-secondary" href="<?= htmlspecialchars(crunchlabs_url('admin/products.php')); ?>">Manage Products</a>
</section>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
