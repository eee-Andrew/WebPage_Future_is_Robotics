<?php
$pageTitle = 'Sign in';
$showHero = false;
$bodyClass = 'auth-page';
require_once __DIR__ . '/db.php';

$errors = [];
$email = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? roboforge_url('account.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
            ];

            roboforge_flash_set('success', 'Welcome back, ' . $user['name'] . '!');
            header('Location: ' . $redirect);
            exit;
        }
    }
}

require_once __DIR__ . '/partials/header.php';
?>
<section class="auth-form">
    <h2 data-i18n="auth.loginTitle">Sign in to your account</h2>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endforeach; ?>
    <form method="post" class="form-card">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect); ?>">
        <div class="form-field">
            <label class="form-label" for="login-email" data-i18n="auth.email">Email</label>
            <input id="login-email" type="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($email); ?>">
        </div>
        <div class="form-field">
            <label class="form-label" for="login-password" data-i18n="auth.password">Password</label>
            <input id="login-password" type="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="cta-button" data-i18n="auth.loginButton">Log in</button>
    </form>
    <p><span data-i18n="auth.registerPromptText">Need an account?</span> <a href="<?= htmlspecialchars(roboforge_url('register.php?redirect=' . urlencode($redirect))); ?>" data-i18n="auth.registerLink">Create one</a>.</p>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
