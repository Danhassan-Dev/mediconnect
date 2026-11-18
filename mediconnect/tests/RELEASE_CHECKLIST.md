# MediConnect Release Readiness Checklist

Do not deploy for real patient use until all applicable items have been reviewed by qualified technical, clinical, privacy and legal professionals.

## Technical
- [ ] Remove test accounts and sample clinical data.
- [ ] Use supported PHP and MySQL versions.
- [ ] Configure production database credentials with least privilege.
- [ ] Store SMTP/SMS secrets outside web root and source control.
- [ ] Disable PHP display_errors in production; log errors securely.
- [ ] Enforce HTTPS and secure session cookie settings.
- [ ] Add login throttling, monitoring and alerting.
- [ ] Review authorization on every route and record-level action.
- [ ] Test concurrent booking and duplicate submission behavior.
- [ ] Implement backups, retention and restore procedures.
- [ ] Add queue monitoring, retry limits and dead-letter/manual review.
- [ ] Conduct independent security testing and remediate findings.

## Clinical and operational
- [ ] Independently verify clinician identity and current professional registration.
- [ ] Define clinical governance, escalation and emergency-care messaging.
- [ ] Define appointment cancellation, rescheduling and no-show policies.
- [ ] Ensure the service does not claim to diagnose or replace emergency care.
- [ ] Obtain clinical review of all patient-facing medical content.

## Privacy and compliance
- [ ] Identify applicable Nigerian data protection and health-sector requirements.
- [ ] Complete a privacy impact assessment with qualified counsel.
- [ ] Publish clear privacy notice, consent and retention rules.
- [ ] Collect only necessary data and restrict staff access.
- [ ] Define incident response and data-subject request procedures.
- [ ] Obtain consent and provide opt-out for SMS/email where required.
- [ ] Ensure notification content avoids sensitive health information.

## Final decision
Release only after the responsible project owner records approval and unresolved risks are accepted by the appropriate professionals.
