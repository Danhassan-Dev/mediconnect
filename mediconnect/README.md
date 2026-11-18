# MediConnect — Phase 2: Patient authentication

This milestone adds a Core PHP + MySQL backend for patient registration, login, logout, and a protected patient dashboard.

## Requirements
- Windows with XAMPP
- Apache and MySQL running
- PHP with PDO MySQL enabled (standard XAMPP PHP includes it)
- VS Code or another code editor

## Installation
1. Extract the `mediconnect_phase2` folder.
2. Copy it into `C:\xampp\htdocs\`.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Select the **Import** tab, choose `database.sql`, and click **Import**.
5. Open `config/database.php`. Confirm the host, database name, MySQL username and password. The defaults are `127.0.0.1`, `mediconnect`, `root`, and an empty password.
6. Start Apache and MySQL from the XAMPP Control Panel.
7. Visit `http://localhost/mediconnect_phase2/`.

## Test the milestone
1. Choose **Create an account**.
2. Register with a test name, email address, optional phone number, and password of at least 10 characters.
3. Confirm that the patient dashboard opens.
4. Click **Sign out**.
5. Sign in using the same email and password.
6. Try opening `http://localhost/mediconnect_phase2/dashboard.php` after signing out. It should redirect to the login page.

## What is implemented
- PDO database connection with exception mode and native prepared statements.
- Patient registration and duplicate-email checking.
- Password hashing with `password_hash()` and verification with `password_verify()`.
- Session ID regeneration after authentication.
- CSRF tokens on registration, login, and logout forms.
- Output escaping for displayed user data.
- Patient-only route protection.
- Database tables prepared for patient profiles, doctor profiles, and appointments.

## Important limitations
- This is a development milestone, not a production-ready medical system.
- Email/SMS notifications, email verification, password reset, appointment booking, doctor/admin portals, and audit logging are not implemented yet.
- Do not enter real patient or medical information into a local prototype.
- Before deployment, add HTTPS, rate limiting, secure password-reset and email-verification flows, role-specific authorization, audit logging, backups, and a privacy/security review. Do not store or transmit real patient information until those controls are implemented and tested.
- The application is not an emergency service.
