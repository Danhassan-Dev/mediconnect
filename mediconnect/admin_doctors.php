<?php
require_once __DIR__ . '/includes/admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $doctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
    $decision = $_POST['decision'] ?? '';
    if (!$doctorId || !in_array($decision, ['approve','unapprove'], true)) {
        flash('error', 'Invalid doctor application action.');
    } else {
        $stmt = $pdo->prepare("UPDATE doctor_profiles SET is_approved=? WHERE user_id=?");
        $stmt->execute([$decision === 'approve' ? 1 : 0, $doctorId]);
        flash('success', $decision === 'approve' ? 'Doctor marked as approved.' : 'Doctor approval withdrawn.');
    }
    header('Location: admin_doctors.php'); exit;
}

$doctors = $pdo->query(
    "SELECT u.id, u.full_name, u.email, u.status, dp.specialty, dp.qualification,
            dp.license_number, dp.years_experience, dp.is_approved
     FROM users u JOIN doctor_profiles dp ON dp.user_id=u.id
     WHERE u.role='doctor' ORDER BY dp.is_approved ASC, u.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Doctor applications | MediConnect</title><link rel="stylesheet" href="assets/css/admin.css"></head><body>
<header class="topbar"><a class="brand" href="admin_dashboard.php">MediConnect <span>Admin</span></a><nav><a href="admin_dashboard.php">Overview</a><a href="admin_appointments.php">Appointments</a><a href="admin_users.php">Users</a><a href="logout.php">Logout</a></nav></header>
<main class="wrap"><div class="heading"><div><p class="eyebrow">CREDENTIAL WORKFLOW</p><h1>Doctor applications</h1><p class="muted">Review submitted information before enabling a doctor account.</p></div></div>
<?php $notice=get_flash(); if($notice): ?><div class="notice"><?= e(is_array($notice)?($notice['message']??''):$notice) ?></div><?php endif; ?>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Doctor</th><th>Specialty</th><th>Qualification</th><th>License number</th><th>Experience</th><th>Approval</th><th>Action</th></tr></thead><tbody>
<?php if(!$doctors): ?><tr><td colspan="7">No doctor profiles found.</td></tr><?php endif; ?>
<?php foreach($doctors as $d): ?><tr><td><strong><?= e($d['full_name']) ?></strong><small><?= e($d['email']) ?></small></td><td><?= e($d['specialty']) ?></td><td><?= e($d['qualification']) ?></td><td><?= e($d['license_number']) ?></td><td><?= e((string)($d['years_experience'] ?? '—')) ?></td><td><?= $d['is_approved'] ? 'Approved' : 'Pending' ?></td><td>
<form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="doctor_id" value="<?= (int)$d['id'] ?>">
<?php if(!$d['is_approved']): ?><button name="decision" value="approve" class="btn">Approve</button><?php else: ?><button name="decision" value="unapprove" class="btn secondary">Withdraw</button><?php endif; ?></form>
</td></tr><?php endforeach; ?>
</tbody></table></div></section>
<p class="warning">Approval in this prototype is an administrative flag only. Verify the clinician's identity, license and credentials with the relevant professional regulator before approving access.</p>
</main></body></html>
