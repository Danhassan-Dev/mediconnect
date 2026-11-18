<?php
/**
 * Copy this file to notification_config.php and fill in your provider details.
 * Keep notification_config.php out of GitHub/public repositories.
 */
return [
    'email' => [
        'enabled' => false,
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'your-smtp-username',
        'password' => 'your-smtp-password',
        'encryption' => 'tls', // tls or ssl
        'from_email' => 'notifications@example.com',
        'from_name' => 'MediConnect',
    ],
    'sms' => [
        'enabled' => false,
        // SMS integration is intentionally disabled until you choose a gateway
        // and add its official API implementation.
    ],
];
