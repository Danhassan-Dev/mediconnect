<?php
// Run from command line: php notifications/worker.php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}
require_once __DIR__ . '/../includes/notification_helpers.php';
require_once __DIR__ . '/send_email.php';
require_once __DIR__ . '/send_sms.php';

$configFile = __DIR__ . '/../config/notification_config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Missing config/notification_config.php. Copy the example and configure SMTP.\n");
    exit(1);
}
$config = require $configFile;
$maxPerRun = 25;
$processed = 0;

while ($processed < $maxPerRun) {
    // Claim one row atomically. This simple worker is intended for one local
    // scheduled task; use a dedicated queue service for multi-worker production.
    $pdo->beginTransaction();
    $stmt = $pdo->query(
        "SELECT * FROM notification_queue
         WHERE status IN ('queued','failed') AND attempts < 5
           AND scheduled_at <= NOW()
         ORDER BY created_at ASC LIMIT 1 FOR UPDATE"
    );
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) {
        $pdo->commit();
        break;
    }
    $claim = $pdo->prepare(
        "UPDATE notification_queue SET status='processing', attempts=attempts+1 WHERE id=?"
    );
    $claim->execute([$job['id']]);
    $pdo->commit();

    try {
        $recipient = notification_recipient($pdo, (int)$job['user_id']);
        if (!$recipient) {
            throw new RuntimeException('Active recipient account not found.');
        }
        if ($job['channel'] === 'email') {
            if (empty($config['email']['enabled'])) {
                throw new RuntimeException('Email is disabled in notification configuration.');
            }
            send_notification_email($config['email'], $recipient['email'], $job['subject'] ?? 'MediConnect notification', $job['message']);
        } else {
            if (empty($config['sms']['enabled'])) {
                throw new RuntimeException('SMS is disabled in notification configuration.');
            }
            $phoneStmt = $pdo->prepare('SELECT phone FROM users WHERE id = ? AND status = "active" LIMIT 1');
            $phoneStmt->execute([(int)$job['user_id']]);
            $phone = $phoneStmt->fetchColumn();
            if (!$phone) {
                throw new RuntimeException('No phone number found. Add a verified phone field to the account first.');
            }
            send_notification_sms($config['sms'], $phone, $job['message']);
        }
        $done = $pdo->prepare("UPDATE notification_queue SET status='sent', sent_at=NOW(), last_error=NULL WHERE id=?");
        $done->execute([$job['id']]);
        echo "Sent notification #{$job['id']}.\n";
    } catch (Throwable $e) {
        $failed = $pdo->prepare(
            "UPDATE notification_queue
             SET status=IF(attempts >= 5, 'failed', 'queued'),
                 scheduled_at=DATE_ADD(NOW(), INTERVAL LEAST(60, POW(2, attempts)) MINUTE),
                 last_error=? WHERE id=?"
        );
        $failed->execute([mb_substr($e->getMessage(), 0, 500), $job['id']]);
        error_log('MediConnect notification #' . $job['id'] . ': ' . $e->getMessage());
        echo "Notification #{$job['id']} failed; it will be retried if attempts remain.\n";
    }
    $processed++;
}
echo "Worker finished. Processed: {$processed}\n";
