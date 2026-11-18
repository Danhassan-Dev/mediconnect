USE mediconnect;
ALTER TABLE appointments MODIFY status ENUM('pending','confirmed','rescheduled','cancelled','completed','rejected') NOT NULL DEFAULT 'pending';
CREATE INDEX idx_appointments_doctor_slot_status ON appointments(doctor_id,appointment_date,appointment_time,status);
