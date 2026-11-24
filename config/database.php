<?php
declare(strict_types=1);
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'mt_zion_school';
$user = getenv('DB_USER') ?: 'root'; // Local WAMP default only; set DB_USER on production.
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''; // Set a strong password on production.
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check the server configuration.');
}
