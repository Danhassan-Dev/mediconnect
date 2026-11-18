<?php
require_once __DIR__ . '/includes/admin_guard.php';
$users=$pdo->query("SELECT id,full_name,email,role,status FROM users ORDER BY id DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Users | MediConnect</title><link rel="stylesheet" href="assets/css/admin.css"></head><body>
<header class="topbar"><a class="brand" href="admin_dashboard.php">MediConnect <span>Admin</span></a><nav><a href="admin_dashboard.php">Overview</a><a href="admin_doctors.php">Doctors</a><a href="admin_appointments.php">Appointments</a><a href="logout.php">Logout</a></nav></header>
<main class="wrap"><div class="heading"><div><p class="eyebrow">ACCOUNT DIRECTORY</p><h1>Registered users</h1><p class="muted">Read-only account list for administrative monitoring.</p></div></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>ID</th><th>Full name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead><tbody>
<?php if(!$users): ?><tr><td colspan="5">No users found.</td></tr><?php endif; ?>
<?php foreach($users as $u): ?><tr><td><?= (int)$u['id'] ?></td><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e(ucfirst($u['role'])) ?></td><td><?= e(ucfirst($u['status'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section><p class="muted">Showing up to 300 accounts. Account modification and deletion are intentionally not included.</p></main></body></html>
