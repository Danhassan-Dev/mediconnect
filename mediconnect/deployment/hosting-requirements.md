# Hosting Requirements and Deployment Layout

## Required capabilities
- Supported PHP 8.x release (choose a version supported by both your host and application dependencies).
- MySQL 8.x or compatible MariaDB version supported by the host.
- PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `json`.
- Apache with `mod_rewrite` and `.htaccess` support, or equivalent Nginx rules.
- HTTPS certificate, protected error logs, database backups and scheduled tasks/cron.
- Outbound SMTP access if email notifications are enabled.

## Recommended layout
Prefer storing private application code/configuration outside the public document root:

```text
/home/account/
  mediconnect_app/
    config/
    includes/
    vendor/
    storage/
  public_html/
    index.php
    login.php
    assets/
    .htaccess
```

The existing project is a phase-by-phase prototype and may currently keep PHP pages, includes and config beneath one folder. Moving files into this layout requires updating include paths and entry points; do not move folders without testing.

## Shared hosting deployment sequence
1. Create the database and a dedicated database user in the hosting control panel.
2. Upload the reviewed application over SFTP or the host's secure file manager.
3. Upload dependencies using Composer on the server or a controlled build machine. Do not upload development-only files.
4. Configure production secrets outside the public directory.
5. Import reviewed schema migrations after taking a database backup.
6. Configure the domain document root and HTTPS.
7. Configure the notification worker as a scheduled CLI/cron task, if email is configured.
8. Run smoke tests with test accounts and confirm logs/backup jobs.
9. Remove test users, temporary diagnostic pages and setup scripts.

## Important
Do not use XAMPP as an internet-facing production server. It is a local development environment. Do not expose phpMyAdmin publicly unless protected by the hosting provider's secure administrative controls.
