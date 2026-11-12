<?php

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Gear Gauge — Login';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$loginError = '';
$submittedEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedEmail = trim($_POST['email'] ?? '');
    $submittedPassword = $_POST['password'] ?? '';

    if ($submittedEmail === '' || $submittedPassword === '') {
        $loginError = 'Please fill in both email and password.';
    } else {
        $loginStatement = $dbConnection->prepare(
            'SELECT id, username, password_hash, role FROM users WHERE email = ?'
        );
        $loginStatement->bind_param('s', $submittedEmail);
        $loginStatement->execute();
        $loginUser = $loginStatement->get_result()->fetch_assoc();
        $loginStatement->close();

        if ($loginUser !== null && password_verify($submittedPassword, $loginUser['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$loginUser['id'];
            $_SESSION['username'] = $loginUser['username'];
            $_SESSION['role'] = $loginUser['role'];
            $_SESSION['flash_message'] = 'Welcome back, ' . $loginUser['username'] . '!';
            $_SESSION['flash_type'] = 'success';
            header('Location: index.php');
            exit;
        }
        $loginError = 'Invalid email or password.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="card auth-card">
    <h1>Login</h1>
    <p class="muted">Sign in to save and revisit your device comparisons.</p>
    <?php if ($loginError !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php" data-validate novalidate>
        <div class="form-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($submittedEmail) ?>" data-required data-type="email" placeholder="you@example.com">
            <small class="field-error"></small>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" data-required>
            <small class="field-error"></small>
        </div>
        <button type="submit" class="btn btn-block">Login</button>
    </form>
    <p class="auth-switch">No account yet? <a href="register.php">Register here</a>.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
