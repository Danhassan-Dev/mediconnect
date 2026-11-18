<?php
require_once __DIR__ . '/includes/admin_guard.php';

$stats = [
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'patients' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn(),
    'doctors' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='doctor'")->fetchColumn(),
    'pending_doctors' => (int)$pdo->query("SELECT COUNT(*) FROM doctor_profiles WHERE is_approved=0")->fetchColumn(),
    'appointments' => (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
    'pending_appointments' => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn(),
];

$recent = $pdo->query(
    "SELECT a.id, a.appointment_date, a.appointment_time, a.status,
            p.full_name AS patient_name, d.full_name AS doctor_name
     FROM appointments a
     JOIN users p ON p.id=a.patient_id
     JOIN users d ON d.id=a.doctor_id
     ORDER BY a.id DESC LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>MediConnect | Admin Dashboard</title><link rel="stylesheet" href="assets/css/admin.css"></head>
<body>
<header class="topbar"><a class="brand" href="admin_dashboard.php">MediConnect <span>Admin</span></a><nav><a href="admin_doctors.php">Doctors</a><a href="admin_appointments.php">Appointments</a><a href="admin_users.php">Users</a><a href="logout.php">Logout</a></nav></header>
<main class="wrap">
<div class="heading"><div><p class="eyebrow">ADMINISTRATION</p><h1>System overview</h1><p class="muted">Monitor accounts, doctor applications and appointments.</p></div></div>
<section class="stats">
<?php foreach ([
['Total accounts',$stats['users']],['Patients',$stats['patients']],['Doctors',$stats['doctors']],
['Pending doctor applications',$stats['pending_doctors']],['Appointments',$stats['appointments']],['Pending appointments',$stats['pending_appointments']]
] as [$label,$value]): ?>
<article class="stat"><p><?= e($label) ?></p><strong><?= number_format($value) ?></strong></article>
<?php endforeach; ?>
</section>
<section class="panel"><div class="panel-head"><h2>Recent appointments</h2><a href="admin_appointments.php">View all</a></div>
<div class="table-wrap"><table><thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody>
<?php if (!$recent): ?><tr><td colspan="5">No appointments found.</td></tr><?php endif; ?>
<?php foreach($recent as $item): ?><tr>
<td><?= e($item['patient_name']) ?></td><td><?= e($item['doctor_name']) ?></td>
<td><?= e($item['appointment_date']) ?></td><td><?= e($item['appointment_time']) ?></td>
<td><span class="status"><?= e(ucfirst($item['status'])) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div></section>
</main></body></html>
