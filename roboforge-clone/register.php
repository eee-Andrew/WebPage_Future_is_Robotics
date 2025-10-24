<?php
$pageTitle = 'Create account';
$showHero = false;
$bodyClass = 'auth-page';
require_once __DIR__ . '/db.php';

$errors = [];
$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? roboforge_url('account.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $existing = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $existing->execute([$email]);
        if ($existing->fetch()) {
            $errors[] = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $insert->execute([$name, $email, $hash]);

            $_SESSION['user'] = [
                'id' => (int) $pdo->lastInsertId(),
                'email' => $email,
                'name' => $name,
            ];

            roboforge_flash_set('success', 'Welcome aboard, ' . $name . '!');
            header('Location: ' . $redirect);
            exit;
        }
    }
}

require_once __DIR__ . '/partials/header.php';
?>
<section class="auth-form">
    <h2 data-i18n="auth.registerTitle">Create your account</h2>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endforeach; ?>
    <form method="post" class="form-card">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect); ?>">
        <label data-i18n="auth.name">Full name
            <input type="text" name="name" required value="<?= htmlspecialchars($name); ?>">
        </label>
        <label data-i18n="auth.email">Email
            <input type="email" name="email" required value="<?= htmlspecialchars($email); ?>">
        </label>
        <label data-i18n="auth.password">Password
            <input type="password" name="password" required>
        </label>
        <label data-i18n="auth.confirm">Confirm password
            <input type="password" name="confirm_password" required>
        </label>
        <button type="submit" class="cta-button" data-i18n="auth.registerButton">Create account</button>
    </form>
    <p><span data-i18n="auth.loginPromptText">Already have an account?</span> <a href="<?= htmlspecialchars(roboforge_url('login.php?redirect=' . urlencode($redirect))); ?>" data-i18n="auth.loginLink">Log in</a>.</p>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
