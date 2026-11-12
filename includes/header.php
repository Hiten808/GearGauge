<?php

require_once __DIR__ . '/db.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Gear Gauge') ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="index.php">Gear<span>Gauge</span></a>
        <ul class="nav-links">
            <li><a href="index.php"<?= $currentPage === 'index.php' ? ' class="active"' : '' ?>>Compare</a></li>
            <li><a href="browse.php"<?= $currentPage === 'browse.php' ? ' class="active"' : '' ?>>Browse Devices</a></li>
            <?php if ($isAdmin): ?>
                <li><a href="admin.php"<?= $currentPage === 'admin.php' ? ' class="active"' : '' ?>>Admin Panel</a></li>
            <?php endif; ?>
            <?php if ($isLoggedIn): ?>
                <li><a href="profile.php"<?= $currentPage === 'profile.php' ? ' class="active"' : '' ?>>My Comparisons</a></li>
                <li><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php"<?= $currentPage === 'login.php' ? ' class="active"' : '' ?>>Login</a></li>
                <li><a href="register.php"<?= $currentPage === 'register.php' ? ' class="active"' : '' ?>>Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="container page-content">
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>
