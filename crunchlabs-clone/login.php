<?php
require_once __DIR__ . '/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Email and password required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid credentials.';
        } else {
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/partials/header.php';
?>
<section class="auth-form">
    <h2>Welcome Back</h2>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endforeach; ?>
    <?php if (!empty($_GET['registered'])): ?>
        <div class="alert alert-success">Registration successful! Please log in.</div>
    <?php endif; ?>
    <form method="post">
        <label>Email<input type="email" name="email" required></label>
        <label>Password<input type="password" name="password" required></label>
        <button type="submit" class="cta-button">Login</button>
    </form>
    <p>No account? <a href="register.php">Create one</a>.</p>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
