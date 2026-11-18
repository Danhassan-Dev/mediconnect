<?php
require_once __DIR__ . '/includes/admin_guard.php';

$checks = [];
function add_check(array &$checks, string $name, bool $ok, string $detail): void {
    $checks[] = ['name' => $name, 'ok' => $ok, 'detail' => $detail];
}

try {
    $pdo->query('SELECT 1');
    add_check($checks, 'Database connection', true, 'PDO successfully executed a test query.');
} catch (Throwable $e) {
    add_check($checks, 'Database connection', false, 'Database test query failed. Check the PHP error log.');
}

$requiredTables = ['users','doctor_profiles','appointments','doctor_availability','notification_queue'];
foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = (bool)$stmt->fetchColumn();
        add_check($checks, "Table: {$table}", $exists, $exists ? 'Table exists.' : 'Table is missing; apply the relevant project SQL migration.');
    } catch (Throwable $e) {
        add_check($checks, "Table: {$table}", false, 'Could not inspect table.');
    }
}

$requiredColumns = [
    'users' => ['id','full_name','email','password_hash','role','status'],
    'doctor_profiles' => ['user_id','specialty','qualification','license_number','is_approved'],
    'appointments' => ['id','patient_id','doctor_id','appointment_date','appointment_time','status'],
];
foreach ($requiredColumns as $table => $columns) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
        $actual = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
        $missing = array_values(array_diff($columns, $actual));
        add_check($checks, "Columns: {$table}", !$missing, $missing ? 'Missing: ' . implode(', ', $missing) : 'Required columns found.');
    } catch (Throwable $e) {
        add_check($checks, "Columns: {$table}", false, 'Table missing or could not be inspected.');
    }
}

add_check($checks, 'PHP version', version_compare(PHP_VERSION, '8.0.0', '>='), 'Detected PHP ' . PHP_VERSION . '. Use a currently supported PHP release for deployment.');
foreach (['pdo_mysql','mbstring','openssl','json'] as $extension) {
    add_check($checks, "PHP extension: {$extension}", extension_loaded($extension), extension_loaded($extension) ? 'Loaded.' : 'Not loaded.');
}
add_check($checks, 'HTTPS', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'Local HTTP is acceptable for localhost testing; production must use HTTPS.');
add_check($checks, 'Notification config', is_file(__DIR__ . '/config/notification_config.php'), is_file(__DIR__ . '/config/notification_config.php') ? 'Configuration file exists; verify credentials without displaying them.' : 'Optional until SMTP is configured. Do not expose credentials.');
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MediConnect System Health</title><link rel="stylesheet" href="assets/css/admin.css"><style>.check{display:grid;grid-template-columns:180px 1fr;gap:12px;padding:14px 4px;border-bottom:1px solid #edf0f5}.pass{color:#087d52;font-weight:700}.fail{color:#b42318;font-weight:700}@media(max-width:600px){.check{grid-template-columns:1fr;gap:3px}}</style></head><body>
<header class="topbar"><a class="brand" href="admin_dashboard.php">MediConnect <span>Admin</span></a><nav><a href="admin_dashboard.php">Overview</a><a href="admin_doctors.php">Doctors</a><a href="admin_appointments.php">Appointments</a><a href="logout.php">Logout</a></nav></header>
<main class="wrap"><div class="heading"><div><p class="eyebrow">INTEGRATION CHECK</p><h1>System health</h1><p class="muted">A configuration diagnostic, not a security certification.</p></div></div>
<section class="panel">
<?php $passed=count(array_filter($checks,fn($c)=>$c['ok'])); ?>
<h2><?= $passed ?> of <?= count($checks) ?> checks passed</h2>
<?php foreach($checks as $c): ?><div class="check"><strong><?= e($c['name']) ?></strong><div><span class="<?= $c['ok']?'pass':'fail' ?>"><?= $c['ok']?'PASS':'CHECK' ?></span><div class="muted"><?= e($c['detail']) ?></div></div></div><?php endforeach; ?>
</section><p class="warning">This page checks basic configuration only. It does not test clinical correctness, real email/SMS delivery, penetration resistance or regulatory compliance.</p></main></body></html>
