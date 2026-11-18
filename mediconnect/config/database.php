<?php
declare(strict_types=1);

$host = '127.0.0.1';
$dbname = 'mediconnect';
$username = 'root';
$password = ''; // Default XAMPP MySQL password is often empty. Change if yours is different.

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $exception) {
    error_log('MediConnect database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('The application could not connect to its database. Check config/database.php and confirm MySQL is running.');
}
