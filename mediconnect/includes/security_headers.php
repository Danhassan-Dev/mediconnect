<?php
/**
 * Optional baseline headers.
 * Include near the beginning of each PHP entry point, before any output.
 * Test locally first. Configure HTTPS at the web-server level for production.
 */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: DENY');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    // Enable HSTS only after HTTPS is correctly configured for the whole domain.
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
