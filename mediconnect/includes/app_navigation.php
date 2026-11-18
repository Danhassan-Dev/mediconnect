<?php
/**
 * Optional shared navigation.
 * Include after includes/bootstrap.php and before page output.
 * Uses the role stored in the authenticated session.
 */
$mcRole = $_SESSION['role'] ?? 'guest';
$mcLinks = [
    'patient' => [
        ['Dashboard','dashboard.php'],
        ['Find doctors','doctor_directory.php'],
        ['Book appointment','book_appointment.php'],
        ['My appointments','my_appointments.php'],
    ],
    'doctor' => [
        ['Dashboard','doctor_dashboard.php'],
        ['Availability','doctor_availability.php'],
        ['Appointments','doctor_appointments.php'],
    ],
    'admin' => [
        ['Overview','admin_dashboard.php'],
        ['Doctors','admin_doctors.php'],
        ['Appointments','admin_appointments.php'],
        ['Users','admin_users.php'],
        ['System health','system_health.php'],
    ],
];
$mcCurrent = basename($_SERVER['PHP_SELF'] ?? '');
?>
<header class="mc-topbar">
  <div class="mc-container mc-topbar-inner">
    <a class="mc-brand" href="<?= e(($mcRole === 'admin') ? 'admin_dashboard.php' : (($mcRole === 'doctor') ? 'doctor_dashboard.php' : 'dashboard.php')) ?>">Medi<span>Connect</span></a>
    <button class="mc-menu-toggle" type="button" aria-expanded="false" aria-controls="mc-navigation" onclick="const n=document.getElementById('mc-navigation');const o=this.getAttribute('aria-expanded')==='true';this.setAttribute('aria-expanded',String(!o));n.classList.toggle('is-open',!o)">Menu</button>
    <nav class="mc-nav" id="mc-navigation" aria-label="Main navigation">
      <?php foreach (($mcLinks[$mcRole] ?? []) as [$label,$href]): ?>
        <a href="<?= e($href) ?>" <?= $mcCurrent === $href ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <?php if ($mcRole !== 'guest'): ?><a href="logout.php">Logout</a><?php else: ?><a href="login.php">Login</a><?php endif; ?>
    </nav>
  </div>
</header>
