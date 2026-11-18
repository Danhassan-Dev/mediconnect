CREATE DATABASE IF NOT EXISTS mediconnect
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mediconnect;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'admin') NOT NULL DEFAULT 'patient',
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    email_verified_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS patient_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    phone VARCHAR(30) NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    emergency_contact_name VARCHAR(120) NULL,
    emergency_contact_phone VARCHAR(30) NULL,
    CONSTRAINT fk_patient_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doctor_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    specialty VARCHAR(120) NOT NULL,
    qualification VARCHAR(160) NULL,
    license_number VARCHAR(100) NULL,
    biography TEXT NULL,
    consultation_fee DECIMAL(10,2) NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_doctor_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    symptoms TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'rescheduled', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_patient FOREIGN KEY (patient_id)
        REFERENCES users(id),
    CONSTRAINT fk_appointment_doctor FOREIGN KEY (doctor_id)
        REFERENCES users(id),
    INDEX idx_appointments_patient (patient_id, appointment_date),
    INDEX idx_appointments_doctor_time (doctor_id, appointment_date, appointment_time)
) ENGINE=InnoDB;
