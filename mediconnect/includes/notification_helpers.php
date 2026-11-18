<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * Queue a notification. Call this only after the related database transaction
 * has committed. Notifications contain no symptoms, diagnosis or prescription.
 */
function queue_notification(PDO $pdo, int $userId, string $channel, string $subject, string $message): void
{
    if (!in_array($channel, ['email', 'sms'], true)) {
        throw new InvalidArgumentException('Unsupported notification channel.');
    }
    $stmt = $pdo->prepare(
        'INSERT INTO notification_queue (user_id, channel, subject, message)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $channel, $subject, $message]);
}

function queue_appointment_notice(PDO $pdo, int $userId, string $event, string $date, string $time): void
{
    $when = date('D, j M Y', strtotime($date)) . ' at ' . date('g:i A', strtotime($time));
    $messages = [
        'requested' => ['Appointment request received', "Your MediConnect appointment request has been submitted for {$when}. Sign in to view its status."],
        'confirmed' => ['Appointment confirmed', "Your MediConnect appointment is confirmed for {$when}. Sign in to view the details."],
        'rejected' => ['Appointment update', "There is an update to your MediConnect appointment request. Sign in to view its status."],
        'rescheduled' => ['Appointment rescheduled', "Your MediConnect appointment has been rescheduled to {$when}. Sign in to view the details."],
    ];
    if (!isset($messages[$event])) {
        throw new InvalidArgumentException('Unknown appointment notification event.');
    }
    [$subject, $message] = $messages[$event];
    queue_notification($pdo, $userId, 'email', $subject, $message);
}

/** Resolve account contact details at send time; do not store phone/email copies in the queue. */
function notification_recipient(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? AND status = "active" LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
