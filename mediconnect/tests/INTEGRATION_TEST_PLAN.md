# MediConnect Integration Test Plan

Use only fictional accounts and test records. Run tests on localhost/staging, not with real patient information.

## A. Authentication and role access
- [ ] Register a patient with valid details.
- [ ] Reject duplicate email registration.
- [ ] Reject invalid email and weak/invalid inputs according to validation rules.
- [ ] Confirm passwords are stored as hashes, never plaintext.
- [ ] Patient cannot open doctor-only pages.
- [ ] Patient cannot open admin-only pages.
- [ ] Unapproved doctor cannot access doctor dashboard.
- [ ] Approved doctor can log in and access doctor pages.
- [ ] Logout invalidates the session.
- [ ] Direct URL access is denied when logged out.

## B. Doctor application and availability
- [ ] Doctor submits specialty, qualification and license details.
- [ ] New application is not approved by default.
- [ ] Admin can approve a test doctor.
- [ ] Approved doctor appears in patient directory.
- [ ] Unapproved doctor does not appear in patient directory.
- [ ] Doctor can save availability for a weekday.
- [ ] Invalid or overlapping availability is rejected if overlap validation is implemented.

## C. Appointment workflow
- [ ] Patient can book a future available slot.
- [ ] Patient cannot book a past date.
- [ ] Patient cannot book outside doctor's availability.
- [ ] Two patients cannot reserve the same active doctor slot.
- [ ] Doctor can confirm a pending appointment.
- [ ] Doctor can reject a pending appointment.
- [ ] Patient sees updated status.
- [ ] Doctor can reschedule according to allowed workflow.
- [ ] Cancelled/rejected slots become available only according to business rules.
- [ ] Patient cannot view another patient's appointment.
- [ ] Doctor cannot alter an appointment belonging to another doctor.

## D. Notification workflow
- [ ] Appointment creation queues a minimal notification.
- [ ] SMTP worker sends a test message to an address you control.
- [ ] Invalid SMTP credentials produce a failed/retry state.
- [ ] Notification failure does not undo the appointment.
- [ ] Message body does not include symptoms, diagnosis or prescription.
- [ ] SMS remains disabled until a gateway is configured and consent/opt-out is implemented.

## E. Admin workflow
- [ ] Non-admin account receives HTTP 403 for admin pages.
- [ ] Admin dashboard counts match database counts.
- [ ] Admin can approve/withdraw doctor approval with POST + CSRF token.
- [ ] Appointment overview filters return correct statuses.
- [ ] User directory does not expose passwords, hashes or clinical notes.

## F. Security checks
- [ ] All SQL containing user input uses PDO prepared statements.
- [ ] All user-generated output is HTML-escaped.
- [ ] Every state-changing form validates CSRF tokens.
- [ ] Server checks ownership and role on every protected action.
- [ ] Session ID is regenerated after successful login.
- [ ] Cookies use HttpOnly, SameSite and Secure on HTTPS.
- [ ] Login has rate limiting / throttling.
- [ ] File uploads (if introduced) validate MIME, extension, size and storage location.
- [ ] Error details and secrets are not shown to users.
- [ ] Backups are created and restore-tested.
- [ ] Production uses HTTPS and supported PHP/MySQL versions.

## Test evidence
For each test, record: test ID, date, tester, test account role, expected result, actual result, pass/fail, screenshot or sanitized log reference. Never include real patient identifiers or medical details in test evidence.
