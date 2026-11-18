# MediConnect Production Deployment Checklist

This is a deployment preparation guide, not approval to process real patient data.

## 1. Before selecting hosting
- Confirm the host supports a currently supported PHP release, MySQL/MariaDB, PDO MySQL, mbstring, OpenSSL and HTTPS.
- Confirm you can configure PHP settings, cron jobs, database backups, error logs and outbound SMTP.
- Ask the host where the document root is and whether private application files can be stored outside it.
- Confirm the provider's data-centre location, backup policy, breach process and contractual terms meet your organisation's privacy requirements.
- Do not choose hosting solely because it is cheap.

## 2. Prepare the application
- [ ] Complete Phase 7 integration tests; resolve every failed test.
- [ ] Reconcile the actual database schema with all SQL migrations.
- [ ] Remove demo accounts, fake clinical records and debug pages.
- [ ] Verify doctor credentials independently before enabling real clinician accounts.
- [ ] Configure email with a verified sender and test delivery.
- [ ] Keep SMS disabled until gateway integration, consent and opt-out handling are complete.
- [ ] Add notification delivery monitoring and a safe retry/manual review process.
- [ ] Review every protected route for role checks and record ownership.
- [ ] Review CSRF, output escaping, prepared statements, session fixation and login throttling.
- [ ] Review uploads if any are introduced; store outside the public document root.
- [ ] Ensure no patient symptoms, diagnoses or prescriptions appear in email/SMS.
- [ ] Obtain independent security testing and remediate findings.

## 3. Production secrets
Never upload local development credentials or commit secrets to Git.
- Create a production database and a dedicated least-privilege database user.
- Use a unique, strong database password.
- Configure SMTP credentials separately from mailbox login credentials where possible.
- Keep credentials outside the public web root, or in the host's secret/environment-variable facility.
- Restrict file permissions to the web-server account and administrators.
- Do not display `phpinfo()`, configuration files, environment variables or stack traces publicly.
- Rotate any secret that was previously committed or shared.

## 4. Database migration
1. Put the site into maintenance mode.
2. Export a backup of the production database before migration.
3. Apply reviewed SQL migrations in the correct order (project phases 3, 4 and 5 as applicable).
4. Verify table/column names against the live schema; do not run unreviewed SQL blindly.
5. Run the system health page as an administrator, then remove/disable it publicly.
6. Run smoke tests with dedicated test accounts.
7. Keep a tested rollback plan and a backup from immediately before migration.

## 5. Web server and HTTPS
- Point the domain's document root to the application's public directory if the project is restructured for that.
- Enable HTTPS at the hosting provider and redirect HTTP to HTTPS only after the certificate works.
- Set secure session cookies (`Secure`, `HttpOnly`, `SameSite=Lax` or stricter where compatible).
- Disable directory listing and prevent access to private config, source-control and dependency files.
- Set production `display_errors=Off`; log errors to a protected location.
- Set correct timezone and upload/request limits.
- Use a restrictive Content Security Policy only after testing every page and required asset.

## 6. Backups and operations
- Automate encrypted database backups and, where needed, file backups.
- Keep backups separate from the web server and restrict access.
- Test restoration periodically; a backup that has never been restored is unverified.
- Monitor PHP errors, failed logins, notification queue failures and unusual admin activity.
- Define who responds to incidents and how users are notified.
- Define retention/deletion schedules and secure disposal.

## 7. Nigeria-specific legal/privacy review
Have qualified counsel/privacy professionals assess applicable Nigerian data protection requirements, including the Nigeria Data Protection Act 2023 and relevant NDPC guidance, plus any health-sector obligations. Determine controller/processor roles, lawful basis, notices, consent where required, data-subject rights, cross-border transfers, retention, breach response and vendor contracts. This checklist is not legal advice.

## 8. Go-live gate
Do not go live with real patient information until:
- technical owner signs off security testing;
- clinical lead approves the care workflow and emergency messaging;
- privacy/legal lead approves the data processing and notices;
- operational owner approves support, backup, incident and continuity procedures;
- all critical/high-risk findings are fixed or formally accepted by the responsible authority.
