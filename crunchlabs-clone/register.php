<?php
require_once __DIR__ . '/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$email) {
        $errors[] = 'Valid email required.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
            $insert->execute([$email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/partials/header.php';
?>
<section class="auth-form">
    <h2>Create Account</h2>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endforeach; ?>
    <form method="post">
        <label>Email<input type="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"></label>
        <label>Password<input type="password" name="password" required></label>
        <label>Confirm Password<input type="password" name="confirm_password" required></label>
        <button type="submit" class="cta-button">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Log in</a>.</p>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
