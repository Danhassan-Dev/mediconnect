
<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_patient();

$q = trim((string)($_GET['q'] ?? ''));

$sql = '
    SELECT
        u.id AS doctor_id,
        u.full_name,
        dp.specialty,
        dp.qualification,
        dp.years_experience,
        dp.consultation_fee,
        dp.biography
    FROM users u
    JOIN doctor_profiles dp ON dp.user_id = u.id
    WHERE u.role = "doctor"
      AND u.status = "active"
      AND dp.is_approved = 1
';

$args = [];

if ($q !== '') {
    $sql .= '
        AND (
            u.full_name LIKE ?
            OR dp.specialty LIKE ?
        )
    ';

    $args = ['%' . $q . '%', '%' . $q . '%'];
}

$sql .= ' ORDER BY u.full_name';

$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$doctors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Find a Doctor | MediConnect</title>

    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        .search-form {
            display: flex;
            gap: 12px;
            align-items: end;
            margin: 25px 0;
            flex-wrap: wrap;
        }

        .search-form label {
            flex: 1;
            min-width: 220px;
        }

        .doctor-card {
            margin-bottom: 20px;
        }

        .doctor-card h2 {
            margin-bottom: 8px;
        }

        .doctor-specialty {
            color: #00a5e8;
            font-weight: 600;
        }

        .doctor-booking {
            margin-top: 20px;
        }

        .doctor-booking a {
            display: inline-block;
            padding: 12px 20px;
            background: #00a5e8;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        .doctor-booking a:hover {
            background: #087db5;
        }

        .empty-result {
            padding: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

<main class="dashboard-shell">

    <header class="dashboard-header">
        <a class="brand" href="dashboard.php">
            ✚ MediConnect
        </a>

        <a href="dashboard.php">Dashboard</a>
    </header>

    <section class="welcome-panel">
        <h1>Find a Doctor</h1>
        <p class="muted">
            Search for an approved doctor and book your consultation.
        </p>
    </section>

    <!-- Doctor Search -->
    <form method="get" class="search-form">

        <label>
            Search doctor or specialty

            <input
                type="text"
                name="q"
                value="<?= e($q) ?>"
                placeholder="e.g. General Practice"
            >
        </label>

        <button type="submit" class="primary-button">
            Search
        </button>

    </form>

    <!-- Doctor Results -->
    <?php foreach ($doctors as $doctor): ?>

        <article class="profile-panel doctor-card">

            <h2>
                Dr. <?= e($doctor['full_name']) ?>
            </h2>

            <p class="eyebrow doctor-specialty">
                <?= e($doctor['specialty']) ?>
            </p>

            <p>
                <strong>Qualification:</strong>
                <?= e($doctor['qualification']) ?>
            </p>

            <p>
                <strong>Experience:</strong>
                <?= e(
                    (string)($doctor['years_experience'] ?? 'Not stated')
                ) ?> years
            </p>

            <?php if ($doctor['consultation_fee'] !== null): ?>

                <p>
                    <strong>Consultation fee:</strong>
                    ₦<?= e(
                        number_format(
                            (float)$doctor['consultation_fee'],
                            2
                        )
                    ) ?>
                </p>

            <?php endif; ?>

            <p>
                <?= nl2br(e($doctor['biography'] ?? '')) ?>
            </p>

            <!-- Book Appointment Button -->
            <div class="doctor-booking">

                <a href="book_appointment.php?doctor_id=<?= (int)$doctor['doctor_id'] ?>">
                    Book Appointment
                </a>

            </div>

        </article>

    <?php endforeach; ?>

    <?php if (!$doctors): ?>

        <section class="profile-panel empty-result">
            <h2>No approved doctors found</h2>
            <p>Try searching using another doctor's name or specialty.</p>
        </section>

    <?php endif; ?>

</main>

</body>
</html>
