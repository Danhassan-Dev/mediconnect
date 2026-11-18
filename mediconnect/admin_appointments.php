<?php
require_once __DIR__ . '/includes/admin_guard.php';
$allowed = ['all','pending','confirmed','rejected','rescheduled','cancelled','completed'];
$filter = $_GET['status'] ?? 'all';
if (!in_array($filter,$allowed,true)) $filter='all';
$sql = "SELECT a.id,a.appointment_date,a.appointment_time,a.status,p.full_name AS patient_name,d.full_name AS doctor_name
        FROM appointments a JOIN users p ON p.id=a.patient_id JOIN users d ON d.id=a.doctor_id";
$params=[];
if($filter!=='all'){ $sql.=" WHERE a.status=?"; $params[]=$filter; }
$sql.=" ORDER BY a.appointment_date DESC,a.appointment_time DESC LIMIT 250";
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Appointments | MediConnect</title><link rel="stylesheet" href="assets/css/admin.css"></head><body>
<header class="topbar"><a class="brand" href="admin_dashboard.php">MediConnect <span>Admin</span></a><nav><a href="admin_dashboard.php">Overview</a><a href="admin_doctors.php">Doctors</a><a href="admin_users.php">Users</a><a href="logout.php">Logout</a></nav></header>
<main class="wrap"><div class="heading"><div><p class="eyebrow">SCHEDULING</p><h1>Appointments</h1><p class="muted">Read-only administrative view of appointment records.</p></div></div>
<div class="filters"><?php foreach($allowed as $s): ?><a class="<?= $filter===$s?'active':'' ?>" href="?status=<?= e($s) ?>"><?= e(ucfirst($s)) ?></a><?php endforeach; ?></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="6">No appointments found for this filter.</td></tr><?php endif; ?>
<?php foreach($rows as $r): ?><tr><td>#<?= (int)$r['id'] ?></td><td><?= e($r['patient_name']) ?></td><td><?= e($r['doctor_name']) ?></td><td><?= e($r['appointment_date']) ?></td><td><?= e($r['appointment_time']) ?></td><td><?= e(ucfirst($r['status'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section><p class="muted">Showing up to 250 records. No clinical notes or symptoms are displayed here.</p></main></body></html>
