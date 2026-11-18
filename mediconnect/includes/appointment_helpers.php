<?php
/**
 * MediConnect appointment helpers.
 *
 * This file loads the application's existing bootstrap so appointment pages
 * receive the shared PDO connection, session and authentication helpers.
 */
require_once __DIR__ . '/bootstrap.php';

/**
 * Return true only when the account belongs to an active, approved doctor.
 * doctor_profiles.user_id references users.id.
 */
if (!function_exists('doctor_is_approved')) {
    function doctor_is_approved(PDO $pdo, int $doctorId): bool
    {
        $stmt = $pdo->prepare(
            "SELECT dp.is_approved
             FROM doctor_profiles dp
             INNER JOIN users u ON u.id = dp.user_id
             WHERE dp.user_id = :doctor_id
               AND u.role = 'doctor'
               AND u.status = 'active'
             LIMIT 1"
        );
        $stmt->execute(['doctor_id' => $doctorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false && (int) $row['is_approved'] === 1;
    }
}

/**
 * Validate a YYYY-MM-DD date that is today or later.
 */
if (!function_exists('is_valid_future_date')) {
    function is_valid_future_date(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        if ($parsed === false) {
            return false;
        }

        if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return false;
        }

        return $parsed->format('Y-m-d') === $date
            && $date >= (new DateTimeImmutable('today'))->format('Y-m-d');
    }
}

/**
 * Check that a requested 30-minute slot falls completely inside one of the
 * doctor's weekly availability periods and is not already occupied.
 *
 * $time must be HH:MM:SS (or HH:MM). Availability uses weekday values:
 * Sunday=0 through Saturday=6.
 */
if (!function_exists('time_is_in_doctor_availability')) {
    function time_is_in_doctor_availability(
        PDO $pdo,
        int $doctorId,
        string $date,
        string $time
    ): bool {
        if (!is_valid_future_date($date)) {
            return false;
        }

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time)) {
            return false;
        }

        $time = strlen($time) === 5 ? $time . ':00' : $time;
        $slotStart = DateTimeImmutable::createFromFormat('!H:i:s', $time);

        if ($slotStart === false || $slotStart->format('s') !== '00') {
            return false;
        }

        // The booking interface offers 30-minute start times only.
        if (!in_array((int) $slotStart->format('i'), [0, 30], true)) {
            return false;
        }

        $slotEnd = $slotStart->modify('+30 minutes');
        $weekday = (int) (new DateTimeImmutable($date))->format('w');

        $availability = $pdo->prepare(
            'SELECT id
             FROM doctor_availability
             WHERE doctor_id = :doctor_id
               AND weekday = :weekday
               AND is_available = 1
               AND start_time <= :slot_start
               AND end_time >= :slot_end
             LIMIT 1'
        );
        $availability->execute([
            'doctor_id' => $doctorId,
            'weekday' => $weekday,
            'slot_start' => $slotStart->format('H:i:s'),
            'slot_end' => $slotEnd->format('H:i:s'),
        ]);

        if (!$availability->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }

        // Do not allow a same-day appointment to be booked in the past.
        $requestedDateTime = new DateTimeImmutable($date . ' ' . $slotStart->format('H:i:s'));
        if ($requestedDateTime <= new DateTimeImmutable('now')) {
            return false;
        }

        $occupied = $pdo->prepare(
            "SELECT id
             FROM appointments
             WHERE doctor_id = :doctor_id
               AND appointment_date = :appointment_date
               AND appointment_time = :appointment_time
               AND status IN ('pending', 'confirmed', 'rescheduled')
             LIMIT 1"
        );
        $occupied->execute([
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $slotStart->format('H:i:s'),
        ]);

        return $occupied->fetch(PDO::FETCH_ASSOC) === false;
    }
}

/**
 * Return available 30-minute appointment start times for a doctor on a date.
 * The returned values are HH:MM:SS strings, as expected by book_appointment.php.
 */
if (!function_exists('get_doctor_slots')) {
    function get_doctor_slots(PDO $pdo, int $doctorId, string $date): array
    {
        if (!is_valid_future_date($date)) {
            return [];
        }

        $weekday = (int) (new DateTimeImmutable($date))->format('w');

        $stmt = $pdo->prepare(
            'SELECT start_time, end_time
             FROM doctor_availability
             WHERE doctor_id = :doctor_id
               AND weekday = :weekday
               AND is_available = 1
             ORDER BY start_time'
        );
        $stmt->execute([
            'doctor_id' => $doctorId,
            'weekday' => $weekday,
        ]);

        $slots = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $period) {
            $start = DateTimeImmutable::createFromFormat(
                '!H:i:s',
                strlen((string) $period['start_time']) === 5
                    ? $period['start_time'] . ':00'
                    : $period['start_time']
            );
            $end = DateTimeImmutable::createFromFormat(
                '!H:i:s',
                strlen((string) $period['end_time']) === 5
                    ? $period['end_time'] . ':00'
                    : $period['end_time']
            );

            if ($start === false || $end === false || $start >= $end) {
                continue;
            }

            // Align generated starts to 00 or 30 minutes, then advance by 30.
            $minute = (int) $start->format('i');
            if ($minute % 30 !== 0 || $start->format('s') !== '00') {
                $start = $start->setTime(
                    (int) $start->format('H'),
                    $minute < 30 ? 30 : 0,
                    0
                );

                if ($minute >= 30) {
                    $start = $start->modify('+1 hour');
                }
            }

            for ($slot = $start; $slot->modify('+30 minutes') <= $end; $slot = $slot->modify('+30 minutes')) {
                $slotValue = $slot->format('H:i:s');

                if (time_is_in_doctor_availability($pdo, $doctorId, $date, $slotValue)) {
                    $slots[$slotValue] = $slotValue;
                }
            }
        }

        ksort($slots);

        return array_values($slots);
    }
}
