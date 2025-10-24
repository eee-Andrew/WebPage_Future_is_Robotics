<?php
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /crunchlabs-clone/login.php');
    exit;
}

$totalProducts = $pdo->query('SELECT COUNT(*) AS cnt FROM products')->fetch()['cnt'];
$totalUsers = $pdo->query('SELECT COUNT(*) AS cnt FROM users')->fetch()['cnt'];
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
            <h3>Registered Users</h3>
            <p><?= (int)$totalUsers; ?></p>
        </div>
        <div class="stat-card">
            <h3>Low Inventory</h3>
            <p><?= (int)$lowStock; ?></p>
        </div>
    </div>
    <a class="btn-secondary" href="/crunchlabs-clone/admin/products.php">Manage Products</a>
</section>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
