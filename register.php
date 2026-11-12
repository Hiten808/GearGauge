<?php

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Gear Gauge — Register';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$formErrors = [];
$formValues = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formValues['username'] = trim($_POST['username'] ?? '');
    $formValues['email'] = trim($_POST['email'] ?? '');
    $submittedPassword = $_POST['password'] ?? '';
    $submittedConfirmPassword = $_POST['confirm_password'] ?? '';

    if ($formValues['username'] === '' || !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $formValues['username'])) {
        $formErrors['username'] = 'Username must be 3-30 characters using letters, numbers or underscores.';
    }
    if (!filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Please enter a valid email address.';
    }
    if (strlen($submittedPassword) < 6) {
        $formErrors['password'] = 'Password must be at least 6 characters long.';
    }
    if ($submittedPassword !== $submittedConfirmPassword) {
        $formErrors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$formErrors) {
        $duplicateStatement = $dbConnection->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $duplicateStatement->bind_param('ss', $formValues['username'], $formValues['email']);
        $duplicateStatement->execute();
        $duplicateUser = $duplicateStatement->get_result()->fetch_assoc();
        $duplicateStatement->close();

        if ($duplicateUser !== null) {
            $formErrors['general'] = 'That username or email is already registered.';
        } else {
            $hashedPassword = password_hash($submittedPassword, PASSWORD_DEFAULT);
            $defaultRole = 'user';
            $registerStatement = $dbConnection->prepare(
                'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)'
            );
            $registerStatement->bind_param(
                'ssss',
                $formValues['username'],
                $formValues['email'],
                $hashedPassword,
                $defaultRole
            );
            $registerStatement->execute();
            $registerStatement->close();

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$dbConnection->insert_id;
            $_SESSION['username'] = $formValues['username'];
            $_SESSION['role'] = 'user';
            $_SESSION['flash_message'] = 'Registration complete. Welcome to Gear Gauge!';
            $_SESSION['flash_type'] = 'success';
            header('Location: index.php');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="card auth-card">
    <h1>Create Account</h1>
    <p class="muted">Register a free account to save your favourite comparisons.</p>
    <?php if (isset($formErrors['general'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($formErrors['general']) ?></div>
    <?php endif; ?>
    <form method="post" action="register.php" data-validate novalidate>
        <div class="form-field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($formValues['username']) ?>" data-required data-min-length="3">
            <small class="field-error"><?= htmlspecialchars($formErrors['username'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($formValues['email']) ?>" data-required data-type="email" placeholder="you@example.com">
            <small class="field-error"><?= htmlspecialchars($formErrors['email'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" data-required data-min-length="6">
            <small class="field-error"><?= htmlspecialchars($formErrors['password'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" data-required data-match="password">
            <small class="field-error"><?= htmlspecialchars($formErrors['confirm_password'] ?? '') ?></small>
        </div>
        <button type="submit" class="btn btn-block">Register</button>
    </form>
    <p class="auth-switch">Already registered? <a href="login.php">Login here</a>.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
