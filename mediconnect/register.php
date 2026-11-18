<?php
require_once __DIR__ . '/includes/bootstrap.php';
redirect_if_logged_in();

$errors = [];
$fullName = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if ($fullName === '' || mb_strlen($fullName) < 2 || mb_strlen($fullName) > 120) {
        $errors[] = 'Enter your full name (2–120 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,30}$/', $phone)) {
        $errors[] = 'Enter a valid phone number.';
    }
    if (strlen($password) < 10) {
        $errors[] = 'Your password must contain at least 10 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'The passwords do not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute(['email' => $email]);

        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            try {
                $pdo->beginTransaction();

                $insertUser = $pdo->prepare(
                    "INSERT INTO users (full_name, email, password_hash, role)
                     VALUES (:full_name, :email, :password_hash, 'patient')"
                );
                $insertUser->execute([
                    'full_name' => $fullName,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                $userId = (int)$pdo->lastInsertId();
                $insertProfile = $pdo->prepare(
                    'INSERT INTO patient_profiles (user_id, phone) VALUES (:user_id, :phone)'
                );
                $insertProfile->execute(['user_id' => $userId, 'phone' => $phone ?: null]);

                $pdo->commit();

                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = 'patient';
                $_SESSION['full_name'] = $fullName;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: dashboard.php');
                exit;
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('MediConnect registration error: ' . $exception->getMessage());
                $errors[] = 'Registration could not be completed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create patient account | MediConnect</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<main class="auth-shell">
    <section class="auth-card">
        <a class="brand" href="index.php"><span class="brand-mark">M</span><span>MediConnect</span></a>
        <p class="eyebrow">PATIENT PORTAL</p>
        <h1>Create your account</h1>
        <p class="muted">Register to request and manage medical consultations.</p>

        <?php if ($errors): ?>
            <div class="alert error" role="alert">
                <strong>Please correct the following:</strong>
                <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label for="full_name">Full name</label>
            <input id="full_name" name="full_name" type="text" maxlength="120" autocomplete="name" required value="<?= e($fullName) ?>">

            <label for="email">Email address</label>
            <input id="email" name="email" type="email" maxlength="190" autocomplete="email" required value="<?= e($email) ?>">

            <label for="phone">Phone number <span class="optional">(optional)</span></label>
            <input id="phone" name="phone" type="tel" maxlength="30" autocomplete="tel" value="<?= e($phone) ?>">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required>
            <small>Use at least 10 characters.</small>

            <label for="confirm_password">Confirm password</label>
            <input id="confirm_password" name="confirm_password" type="password" minlength="10" autocomplete="new-password" required>

            <button class="primary-button" type="submit">Create account</button>
        </form>
        <p class="switch-link">Already registered? <a href="login.php">Sign in</a></p>
        <p class="fine-print">Use this system for non-emergency consultation requests only. For emergencies, contact local emergency services or visit the nearest hospital.</p>
    </section>
</main>
</body>
</html>
