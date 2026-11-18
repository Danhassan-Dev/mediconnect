
<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Only authenticated doctors can access this page.
if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'doctor') {
    header('Location: login.php');
    exit;
}

// Verify that the doctor is active and approved.
$stmt = $pdo->prepare(
    'SELECT u.full_name, u.email, dp.*
     FROM users u
     JOIN doctor_profiles dp ON dp.user_id = u.id
     WHERE u.id = ?
       AND u.status = "active"
       AND dp.is_approved = 1
     LIMIT 1'
);

$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();

if (!$doctor) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$doctorId = (int) $_SESSION['user_id'];

// Get appointment statistics belonging to this doctor.
$statsStmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_appointments,

        COUNT(DISTINCT CASE
            WHEN status IN ('pending', 'confirmed', 'rescheduled', 'completed')
            THEN patient_id
        END) AS total_patients,

        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END)
            AS pending_appointments,

        SUM(CASE WHEN status IN ('confirmed', 'rescheduled')
            THEN 1 ELSE 0 END) AS confirmed_appointments,

        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END)
            AS completed_appointments

     FROM appointments
     WHERE doctor_id = ?"
);

$statsStmt->execute([$doctorId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get the doctor's next five appointment requests.
$upcomingStmt = $pdo->prepare(
    "SELECT
        a.id,
        a.patient_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        u.full_name AS patient_name,
        u.email AS patient_email

     FROM appointments a
     JOIN users u ON u.id = a.patient_id

     WHERE a.doctor_id = ?
       AND a.appointment_date >= CURDATE()
       AND a.status IN ('pending', 'confirmed', 'rescheduled')

     ORDER BY a.appointment_date ASC, a.appointment_time ASC
     LIMIT 5"
);

$upcomingStmt->execute([$doctorId]);
$upcomingAppointments = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

function appointment_status_class($status)
{
    switch ($status) {
        case 'confirmed':
            return 'status-confirmed';

        case 'pending':
            return 'status-pending';

        case 'rescheduled':
            return 'status-rescheduled';

        case 'completed':
            return 'status-completed';

        default:
            return 'status-default';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Doctor Dashboard | MediConnect</title>

    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        .doctor-content {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px 40px;
        }

        .doctor-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 24px 0;
        }

        .doctor-actions a {
            text-decoration: none;
            display: inline-block;
        }

        .doctor-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin: 24px 0;
        }

        .doctor-stat {
            background: #fff;
            padding: 22px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .doctor-stat h3 {
            font-size: 14px;
            color: #64748b;
            margin: 0 0 12px;
        }

        .doctor-stat strong {
            font-size: 30px;
            color: #0b1f44;
        }

        .doctor-panel {
            background: #fff;
            padding: 24px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-top: 24px;
            overflow-x: auto;
        }

        .doctor-panel h2 {
            margin-top: 0;
            color: #0b1f44;
        }

        .doctor-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .doctor-table th,
        .doctor-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .doctor-table th {
            color: #64748b;
            font-size: 13px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-confirmed {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-rescheduled {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background: #e2e8f0;
            color: #334155;
        }

        .status-default {
            background: #f1f5f9;
            color: #475569;
        }

        .doctor-profile {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-top: 24px;
        }

        .doctor-profile p {
            margin: 12px 0;
        }

        .empty-state {
            padding: 25px 0;
            color: #64748b;
        }

        @media (max-width: 800px) {
            .doctor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .doctor-grid {
                grid-template-columns: 1fr;
            }

            .doctor-content {
                padding: 0 12px 30px;
            }
        }
    </style>
</head>

<body>

<main class="dashboard-shell">

    <!-- HEADER -->
    <header class="dashboard-header">

        <a class="brand" href="doctor_dashboard.php">
            ✚ MediConnect
        </a>

        <form method="post" action="logout.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <button class="secondary-button" type="submit">
                Sign out
            </button>

        </form>

    </header>


    <div class="doctor-content">

        <!-- WELCOME -->
        <section class="welcome-panel">

            <p class="eyebrow">DOCTOR PORTAL</p>

            <h1>
                Welcome, Dr. <?= e($doctor['full_name']) ?>
            </h1>

            <p class="muted">
                Manage your patients, appointments and consultation availability.
            </p>

        </section>


        <!-- QUICK ACTIONS -->
        <nav class="doctor-actions">

            <a href="doctor_appointments.php"
               class="primary-button">
                Manage appointments
            </a>

            <a href="doctor_availability.php"
               class="secondary-button">
                Manage availability
            </a>

        </nav>


        <!-- STATISTICS -->
        <section>

            <h2>Practice overview</h2>

            <div class="doctor-grid">

                <div class="doctor-stat">

                    <h3>Total patients</h3>

                    <strong>
                        <?= (int) $stats['total_patients'] ?>
                    </strong>

                </div>


                <div class="doctor-stat">

                    <h3>Total appointments</h3>

                    <strong>
                        <?= (int) $stats['total_appointments'] ?>
                    </strong>

                </div>


                <div class="doctor-stat">

                    <h3>Pending requests</h3>

                    <strong>
                        <?= (int) $stats['pending_appointments'] ?>
                    </strong>

                </div>


                <div class="doctor-stat">

                    <h3>Confirmed appointments</h3>

                    <strong>
                        <?= (int) $stats['confirmed_appointments'] ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- UPCOMING APPOINTMENTS -->
        <section class="doctor-panel">

            <h2>Upcoming patient appointments</h2>

            <p class="muted">
                Your next five pending or confirmed appointments.
            </p>

            <?php if (empty($upcomingAppointments)): ?>

                <div class="empty-state">

                    <p>You do not have any upcoming appointments yet.</p>

                    <p>
                        Once patients book consultations with you,
                        their requests will appear here.
                    </p>

                </div>

            <?php else: ?>

                <table class="doctor-table">

                    <thead>

                        <tr>
                            <th>Patient</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($upcomingAppointments as $appointment): ?>

                        <tr>

                            <td>
                                <?= e($appointment['patient_name']) ?>
                            </td>

                            <td>
                                <?= e($appointment['patient_email']) ?>
                            </td>

                            <td>
                                <?= e(date(
                                    'd M Y',
                                    strtotime($appointment['appointment_date'])
                                )) ?>
                            </td>

                            <td>
                                <?= e(date(
                                    'h:i A',
                                    strtotime($appointment['appointment_time'])
                                )) ?>
                            </td>

                            <td>

                                <span class="status-badge <?= e(
                                    appointment_status_class($appointment['status'])
                                ) ?>">

                                    <?= e(ucfirst($appointment['status'])) ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

            <p style="margin-top: 20px;">

                <a href="doctor_appointments.php">
                    View and manage all appointments →
                </a>

            </p>

        </section>


        <!-- PROFESSIONAL PROFILE -->
        <section class="doctor-profile">

            <h2>Professional profile</h2>

            <p>
                <b>Name:</b>
                Dr. <?= e($doctor['full_name']) ?>
            </p>

            <p>
                <b>Email:</b>
                <?= e($doctor['email']) ?>
            </p>

            <p>
                <b>Specialty:</b>
                <?= e($doctor['specialty']) ?>
            </p>

            <p>
                <b>Qualification:</b>
                <?= e($doctor['qualification']) ?>
            </p>

            <p>
                <b>License:</b>
                <?= e($doctor['license_number']) ?>
            </p>

            <p>
                <b>Experience:</b>
                <?= e((string)($doctor['years_experience'] ?? 'Not provided')) ?>
                years
            </p>

            <p>
                <b>Consultation fee:</b>

                <?= $doctor['consultation_fee'] === null
                    ? 'Not set'
                    : '₦' . e(number_format(
                        (float)$doctor['consultation_fee'],
                        2
                    ))
                ?>
            </p>

            <p>
                <b>Biography:</b>
            </p>

            <p>
                <?= nl2br(e($doctor['biography'] ?? 'No biography provided.')) ?>
            </p>

        </section>

    </div>

</main>

</body>
</html>
