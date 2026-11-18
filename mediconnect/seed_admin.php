
<?php

$host = 'localhost';
$dbname = 'mediconnect';
$username = 'root';
$db_password = '';

$admin_name = 'MediConnect Administrator';
$admin_email = 'admin@mediconnect.local';
$admin_password = 'ChangeMe@2026!Admin';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $db_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    // Check whether the admin email already exists
    $check = $pdo->prepare(
        "SELECT id FROM users WHERE email = ? LIMIT 1"
    );
    $check->execute([$admin_email]);

    if ($check->fetch()) {
        exit("An account with this email already exists.");
    }

    // Hash the password
    $password_hash = password_hash(
        $admin_password,
        PASSWORD_DEFAULT
    );

    // Create administrator account
    $stmt = $pdo->prepare(
        "INSERT INTO users
        (full_name, email, password_hash, role, status)
        VALUES (?, ?, ?, 'admin', 'active')"
    );

    $stmt->execute([
        $admin_name,
        $admin_email,
        $password_hash
    ]);

    echo "Administrator account created successfully!";

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "An error occurred. Check your PHP error log.";
}
