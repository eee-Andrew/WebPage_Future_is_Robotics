<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /crunchlabs-clone/login.php');
    exit;
}

$errors = [];
$csrfToken = get_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session has expired. Please retry the action.';
        $csrfToken = regenerate_csrf_token();
    } else {
        $action = $_POST['action'] ?? 'create';

        if ($action === 'delete') {
            $productId = (int) ($_POST['product_id'] ?? 0);
            if ($productId > 0) {
                $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
                $stmt->execute([$productId]);
                regenerate_csrf_token();
                header('Location: products.php?deleted=1');
                exit;
            }
            $errors[] = 'Unable to delete the selected product.';
        } else {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $imagePath = trim($_POST['image_path'] ?? '');

            if ($name === '') {
                $errors[] = 'Name required.';
            }
            if ($price === false || $price <= 0) {
                $errors[] = 'Price must be a positive number.';
            }
            if ($quantity === false || $quantity < 0) {
                $errors[] = 'Quantity cannot be negative.';
            }
            if ($imagePath !== '' && strpos($imagePath, '/crunchlabs-clone/assets/img/products/') !== 0) {
                $errors[] = 'Image Path must point to /crunchlabs-clone/assets/img/products/.';
            }

            if (!$errors) {
                $stmt = $pdo->prepare('INSERT INTO products (name, description, price, quantity, image_path) VALUES (?,?,?,?,?)');
                $stmt->execute([
                    $name,
                    $description,
                    $price,
                    $quantity,
                    $imagePath !== '' ? $imagePath : null,
                ]);
                regenerate_csrf_token();
                header('Location: products.php?added=1');
                exit;
            }
        }
    }
}

$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
$showHero = false;
$bodyClass = 'admin-page';
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
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
        <h3>Add New Product</h3>
        <label>Name<input type="text" name="name" required></label>
        <label>Description<textarea name="description" rows="3"></textarea></label>
        <label>Price<input type="number" step="0.01" name="price" required></label>
        <label>Quantity<input type="number" name="quantity" min="0" required></label>
        <label>Image Path<input type="text" name="image_path" placeholder="/crunchlabs-clone/assets/img/products/robot-kit.jpg"></label>
        <button type="submit" class="cta-button">Add Product</button>
        <input type="hidden" name="action" value="create">
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
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this product?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?= (int)$product['id']; ?>">
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
