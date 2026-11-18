
<?php
require_once __DIR__ . '/includes/bootstrap.php';

redirect_if_logged_in();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, password_hash, role, status
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'The email address or password is incorrect.';

        } elseif ($user['status'] !== 'active') {
            $error = 'This account is currently unavailable. Contact the system administrator.';

        } elseif (!in_array($user['role'], ['patient', 'doctor', 'admin'], true)) {
            $error = 'This account has an invalid role. Contact the administrator.';

        } else {

            // Doctors must be approved before they can access their dashboard.
            if ($user['role'] === 'doctor') {
                $doctorStmt = $pdo->prepare(
                    'SELECT is_approved
                     FROM doctor_profiles
                     WHERE user_id = :user_id
                     LIMIT 1'
                );

                $doctorStmt->execute(['user_id' => $user['id']]);
                $doctor = $doctorStmt->fetch();

                if (!$doctor || (int)$doctor['is_approved'] !== 1) {
                    $error = 'Your doctor application is awaiting approval.';
                }
            }

            if ($error === '') {

                // Update password hash if PHP recommends a newer algorithm.
                if (password_needs_rehash(
                    $user['password_hash'],
                    PASSWORD_DEFAULT
                )) {
                    $rehash = $pdo->prepare(
                        'UPDATE users
                         SET password_hash = :hash
                         WHERE id = :id'
                    );

                    $rehash->execute([
                        'hash' => password_hash($password, PASSWORD_DEFAULT),
                        'id' => $user['id'],
                    ]);
                }

                // Create a fresh authenticated session.
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                // Redirect according to account role.
                $destinations = [
                    'patient' => 'dashboard.php',
                    'doctor' => 'doctor_dashboard.php',
                    'admin' => 'admin_dashboard.php'
                ];

                header('Location: ' . $destinations[$user['role']]);
                exit;
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
    <title>Sign in | MediConnect</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body>
<main class="auth-shell">
    <section class="auth-card">

        <a class="brand" href="index.php">
            <span class="brand-mark">M</span>
            <span>MediConnect</span>
        </a>

        <p class="eyebrow">SECURE PORTAL</p>

        <h1>Welcome back</h1>

        <p class="muted">
            Sign in to access your MediConnect account.
        </p>

        <?php if ($error): ?>
            <div class="alert error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="email">Email address</label>

            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                value="<?= e($email) ?>"
            >

            <label for="password">Password</label>

            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
            >

            <button class="primary-button" type="submit">
                Sign in
            </button>

        </form>

        <p class="switch-link">
            New to MediConnect?
            <a href="register.php">Create an account</a>
        </p>

        <p class="fine-print">
            For emergencies, contact local emergency services
            or visit the nearest hospital.
        </p>

    </section>
</main>
</body>
</html>
