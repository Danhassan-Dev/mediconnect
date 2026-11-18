<?php
/**
 * Template only. Do not copy secrets into a publicly accessible directory.
 * Adapt your existing includes/bootstrap.php to load credentials from a
 * protected location or the hosting provider's environment-variable service.
 */
return [
    'database' => [
        'host' => '127.0.0.1',
        'name' => 'YOUR_PRODUCTION_DATABASE',
        'username' => 'YOUR_LIMITED_DATABASE_USER',
        'password' => 'REPLACE_WITH_UNIQUE_SECRET',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'environment' => 'production',
        'debug' => false,
        'timezone' => 'Africa/Lagos',
    ],
];
