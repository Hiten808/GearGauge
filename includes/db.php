<?php

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'specmatrix_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dbConnection = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $dbConnection->set_charset('utf8mb4');
} catch (mysqli_sql_exception $connectionError) {
    http_response_code(500);
    die('Database connection failed. Please check the database settings in includes/db.php');
}

function format_category(string $category): string
{
    return match ($category) {
        'laptop' => 'Laptop',
        'phone' => 'Phone',
        'tablet' => 'Tablet',
        'androidtv' => 'Android TV',
        default => ucfirst($category),
    };
}
