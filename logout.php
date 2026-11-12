<?php

require_once __DIR__ . '/includes/db.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $sessionCookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $sessionCookieParams['path'],
        $sessionCookieParams['domain'],
        $sessionCookieParams['secure'],
        $sessionCookieParams['httponly']
    );
}

session_destroy();

header('Location: index.php');
exit;
