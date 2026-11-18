
<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_patient();

$stmt = $pdo->prepare(
    'SELECT u.full_name, u.email, p.phone
     FROM users u
     LEFT JOIN patient_profiles p ON p.user_id = u.id
     WHERE u.id = :id LIMIT 1'
);

$stmt->execute(['id' => $_SESSION['user_id']]);
$patient = $stmt->fetch();

if (!$patient) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard | MediConnect</title>
    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        .patient-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 25px 0;
        }

        .action-card {
            display: block;
            padding: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #0b1f44;
            background: #fff;
            transition: 0.2s ease;
        }

        .action-card:hover {
            border-color: #00b4ff;
            transform: translateY(-3px);
        }

        .action-card h3 {
            margin: 0 0 8px;
        }

        .action-card p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        .action-icon {
            font-size: 25px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

<main class="dashboard-shell">

    <header class="dashboard-header">
        <a class="brand" href="dashboard.php">
            <span class="brand-mark">M</span>
            <span>MediConnect</span>
        </a>

        <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">
            <button class="secondary-button" type="submit">
                Sign out
            </button>
        </form>
    </header>

    <section class="welcome-panel">
        <p class="eyebrow">PATIENT DASHBOARD</p>

        <h1>Hello, <?= e($patient['full_name']) ?></h1>

        <p class="muted">
            Welcome to MediConnect. Manage your consultations from here.
        </p>
    </section>

    <section class="profile-panel">
        <h2>Patient Services</h2>

        <div class="patient-actions">

            <a href="doctor_directory.php" class="action-card">
                <div class="action-icon">🩺</div>
                <h3>Find a Doctor</h3>
                <p>Browse approved doctors and choose a specialist.</p>
            </a>

            <a href="my_appointments.php" class="action-card">
                <div class="action-icon">📅</div>
                <h3>My Appointments</h3>
                <p>View your appointment requests and their status.</p>
            </a>

        </div>
    </section>

    <section class="profile-panel">
        <h2>Account Information</h2>

        <dl>
            <div>
                <dt>Full name</dt>
                <dd><?= e($patient['full_name']) ?></dd>
            </div>

            <div>
                <dt>Email address</dt>
                <dd><?= e($patient['email']) ?></dd>
            </div>

            <div>
                <dt>Phone number</dt>
                <dd><?= e($patient['phone'] ?: 'Not provided') ?></dd>
            </div>

            <div>
                <dt>Account type</dt>
                <dd>Patient</dd>
            </div>
        </dl>
    </section>

    <p class="fine-print">
        MediConnect is not an emergency service and does not replace
        professional medical assessment.
    </p>

</main>

</body>
</html>
