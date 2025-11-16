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
        <div class="form-field">
            <label class="form-label" for="register-name" data-i18n="auth.name">Full name</label>
            <input id="register-name" type="text" name="name" required autocomplete="name" value="<?= htmlspecialchars($name); ?>">
        </div>
        <div class="form-field">
            <label class="form-label" for="register-email" data-i18n="auth.email">Email</label>
            <input id="register-email" type="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($email); ?>">
        </div>
        <div class="form-field">
            <label class="form-label" for="register-password" data-i18n="auth.password">Password</label>
            <input id="register-password" type="password" name="password" required autocomplete="new-password">
        </div>
        <div class="form-field">
            <label class="form-label" for="register-confirm" data-i18n="auth.confirm">Confirm password</label>
            <input id="register-confirm" type="password" name="confirm_password" required autocomplete="new-password">
        </div>
        <button type="submit" class="cta-button" data-i18n="auth.registerButton">Create account</button>
    </form>
    <p><span data-i18n="auth.loginPromptText">Already have an account?</span> <a href="<?= htmlspecialchars(roboforge_url('login.php?redirect=' . urlencode($redirect))); ?>" data-i18n="auth.loginLink">Log in</a>.</p>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
