<?php
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /crunchlabs-clone/login.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $imagePath = trim($_POST['image_path'] ?? '');

    if ($name === '') {
        $errors[] = 'Name required.';
    }
    if ($price <= 0) {
        $errors[] = 'Price must be positive.';
    }
    if ($quantity < 0) {
        $errors[] = 'Quantity cannot be negative.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO products (name, description, price, quantity, image_path) VALUES (?,?,?,?,?)');
        $stmt->execute([$name, $description, $price, $quantity, $imagePath ?: null]);
        header('Location: products.php?added=1');
        exit;
    }
}

if (!empty($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    header('Location: products.php?deleted=1');
    exit;
}

$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'Manage Products';
require_once __DIR__ . '/../partials/header.php';
?>
<section class="admin-products">
    <h2>Manage Products</h2>
    <?php if (!empty($_GET['added'])): ?>
        <div class="alert alert-success">Product added.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['deleted'])): ?>
        <div class="alert alert-success">Product deleted.</div>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endforeach; ?>

    <form method="post" class="product-form">
        <h3>Add New Product</h3>
        <label>Name<input type="text" name="name" required></label>
        <label>Description<textarea name="description" rows="3"></textarea></label>
        <label>Price<input type="number" step="0.01" name="price" required></label>
        <label>Quantity<input type="number" name="quantity" min="0" required></label>
        <label>Image Path<input type="text" name="image_path" placeholder="/crunchlabs-clone/assets/img/robot-kit.jpg"></label>
        <button type="submit" class="cta-button">Add Product</button>
    </form>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Price</th><th>Quantity</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= (int)$product['id']; ?></td>
                <td><?= htmlspecialchars($product['name']); ?></td>
                <td>$<?= number_format($product['price'], 2); ?></td>
                <td><?= (int)$product['quantity']; ?></td>
                <td>
                    <a class="btn-danger" href="?delete=<?= (int)$product['id']; ?>" onclick="return confirm('Delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
