<?php
require_once __DIR__ . '/../db.php';

$errors = [];
$categories = $pdo->query('SELECT slug, label FROM product_categories ORDER BY label ASC')->fetchAll();
$validCategorySlugs = array_column($categories, 'slug');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($productId > 0) {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            header('Location: products.php?deleted=1');
            exit;
        }
        $errors[] = 'Unable to delete the selected product.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $longDescription = trim($_POST['long_description'] ?? '');
        $categorySlug = trim($_POST['category_slug'] ?? '');
        $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $imagePath = trim($_POST['image_path'] ?? '');
        $storedImagePath = null;

        if ($name === '') {
            $errors[] = 'Name required.';
        }
        if ($shortDescription === '') {
            $errors[] = 'Short description required.';
        }
        if ($longDescription === '') {
            $errors[] = 'Long description required.';
        }
        if (!in_array($categorySlug, $validCategorySlugs, true)) {
            $errors[] = 'Select a valid category.';
        }
        if ($price === false || $price <= 0) {
            $errors[] = 'Price must be a positive number.';
        }
        if ($quantity === false || $quantity < 0) {
            $errors[] = 'Quantity cannot be negative.';
        }
        if ($imagePath !== '') {
            if (preg_match('#^https?://#i', $imagePath)) {
                $storedImagePath = $imagePath;
            } else {
                $normalized = ltrim($imagePath, '/');
                $base = trim(APP_BASE_PATH, '/');
                if ($base !== '' && strpos($normalized, $base . '/') === 0) {
                    $normalized = substr($normalized, strlen($base) + 1);
                }

                if (strpos($normalized, 'assets/img/products/') !== 0) {
                    $errors[] = 'Image Path must point to assets/img/products/ or be an absolute URL.';
                } else {
                    $storedImagePath = $normalized;
                }
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare('INSERT INTO products (name, short_description, long_description, price, quantity, image_path, category_slug) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([
                $name,
                $shortDescription,
                $longDescription,
                $price,
                $quantity,
                $storedImagePath !== null ? $storedImagePath : null,
                $categorySlug,
            ]);
            header('Location: products.php?added=1');
            exit;
        }
    }
}

$products = $pdo->query('SELECT p.*, c.label AS category_label FROM products p JOIN product_categories c ON c.slug = p.category_slug ORDER BY p.created_at DESC')->fetchAll();
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
        <h3>Add New Product</h3>
        <label>Name<input type="text" name="name" required></label>
        <label>Short Description<textarea name="short_description" rows="2" required></textarea></label>
        <label>Long Description<textarea name="long_description" rows="5" required></textarea></label>
        <label>Category
            <select name="category_slug" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['slug']); ?>"><?= htmlspecialchars($category['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Price<input type="number" step="0.01" name="price" required></label>
        <label>Quantity<input type="number" name="quantity" min="0" required></label>
        <label>Image Path<input type="text" name="image_path" placeholder="assets/img/products/robot-kit.jpg"></label>
        <button type="submit" class="cta-button">Add Product</button>
        <input type="hidden" name="action" value="create">
    </form>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Quantity</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= (int)$product['id']; ?></td>
                <td><?= htmlspecialchars($product['name']); ?></td>
                <td><?= htmlspecialchars($product['category_label']); ?></td>
                <td>$<?= number_format($product['price'], 2); ?></td>
                <td><?= (int)$product['quantity']; ?></td>
                <td>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this product?');">
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
