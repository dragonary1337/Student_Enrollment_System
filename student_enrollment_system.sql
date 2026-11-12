-- University of Saint Freya - Student Enrollment System
-- Schema and demo data generated to match SES_copy Laravel migrations.
-- DBMS: MySQL 8 / MariaDB 10.11
--
-- Run against a new database. This script does not drop existing databases
-- or tables. Laravel migrations remain the application's schema source.

CREATE DATABASE IF NOT EXISTS student_enrollment_system_copy_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE student_enrollment_system_copy_db;

-- Nine core SES business tables plus authentication and Laravel framework-support tables (18 tables total)

CREATE TABLE departments (
    department_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_code VARCHAR(10) NOT NULL UNIQUE,
    department_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE programs (
    program_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_code VARCHAR(10) NOT NULL UNIQUE,
    program_name VARCHAR(100) NOT NULL UNIQUE,
    department_id BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_programs_department FOREIGN KEY (department_id)
        REFERENCES departments (department_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE subjects (
    subject_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(10) NOT NULL UNIQUE,
    subject_name VARCHAR(100) NOT NULL,
    units DECIMAL(2,1) NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    CONSTRAINT chk_subjects_units CHECK (units > 0),
    CONSTRAINT fk_subjects_department FOREIGN KEY (department_id)
        REFERENCES departments (department_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE instructors (
    instructor_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NULL UNIQUE,
    department_id BIGINT UNSIGNED NULL,
    CONSTRAINT fk_instructors_department FOREIGN KEY (department_id)
        REFERENCES departments (department_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE students (
    student_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_no VARCHAR(10) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NULL,
    email VARCHAR(100) NULL UNIQUE,
    contact_no VARCHAR(20) NULL,
    program_id BIGINT UNSIGNED NULL,
    year_level TINYINT UNSIGNED NOT NULL,
    status ENUM('Active', 'Inactive', 'Graduated', 'Dropped') NOT NULL DEFAULT 'Active',
    date_registered TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_students_year CHECK (year_level BETWEEN 1 AND 5),
    CONSTRAINT fk_students_program FOREIGN KEY (program_id)
        REFERENCES programs (program_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sections (
    section_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_code VARCHAR(10) NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    instructor_id BIGINT UNSIGNED NULL,
    school_year VARCHAR(9) NOT NULL,
    semester ENUM('1st', '2nd', 'Summer') NOT NULL,
    schedule_day VARCHAR(10) NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    room VARCHAR(10) NULL,
    max_capacity INT UNSIGNED NOT NULL DEFAULT 40,
    CONSTRAINT uq_sections_term UNIQUE (section_code, school_year, semester),
    CONSTRAINT chk_sections_capacity CHECK (max_capacity > 0),
    CONSTRAINT chk_sections_time CHECK (
        start_time IS NULL OR end_time IS NULL OR end_time > start_time
    ),
    CONSTRAINT fk_sections_subject FOREIGN KEY (subject_id)
        REFERENCES subjects (subject_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_sections_instructor FOREIGN KEY (instructor_id)
        REFERENCES instructors (instructor_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payment_methods (
    payment_method_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(50) NOT NULL UNIQUE,
    method_available BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(50) NULL,
    last_name VARCHAR(50) NULL,
    role ENUM('Admin', 'Operator') NOT NULL DEFAULT 'Operator',
    status ENUM('Pending', 'Active', 'Inactive') NOT NULL DEFAULT 'Pending',
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB;

CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration BIGINT NOT NULL,
    INDEX cache_expiration_index (expiration)
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration BIGINT NOT NULL,
    INDEX cache_locks_expiration_index (expiration)
) ENGINE=InnoDB;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
) ENGINE=InnoDB;

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection VARCHAR(255) NOT NULL,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX failed_jobs_connection_queue_failed_at_index (connection, queue, failed_at)
) ENGINE=InnoDB;

CREATE TABLE passkeys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    credential_id VARCHAR(255) NOT NULL UNIQUE,
    credential JSON NOT NULL,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_passkeys_user FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON DELETE CASCADE,
    INDEX passkeys_user_id_index (user_id)
) ENGINE=InnoDB;

CREATE TABLE enrollments (
    enrollment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NOT NULL,
    enrollment_datetime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Enrolled', 'Dropped', 'Completed') NOT NULL DEFAULT 'Enrolled',
    processed_by BIGINT UNSIGNED NULL,
    CONSTRAINT uq_enrollments UNIQUE (student_id, section_id),
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id)
        REFERENCES students (student_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_enrollments_section FOREIGN KEY (section_id)
        REFERENCES sections (section_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_enrollments_user FOREIGN KEY (processed_by)
        REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    school_year VARCHAR(9) NOT NULL,
    semester ENUM('1st', '2nd', 'Summer') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method_id BIGINT UNSIGNED NOT NULL,
    payment_datetime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    recorded_by BIGINT UNSIGNED NULL,
    CONSTRAINT chk_payments_amount CHECK (amount > 0),
    CONSTRAINT fk_payments_student FOREIGN KEY (student_id)
        REFERENCES students (student_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_payments_method FOREIGN KEY (payment_method_id)
        REFERENCES payment_methods (payment_method_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_payments_user FOREIGN KEY (recorded_by)
        REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Demo records from database/seeders/SesDemoSeeder.php

INSERT INTO departments (department_code, department_name)
VALUES ('CCS', 'College of Computer Studies');

INSERT INTO programs (program_code, program_name, department_id)
SELECT 'BSIT', 'Bachelor of Science in Information Technology', department_id
FROM departments WHERE department_code = 'CCS';

INSERT INTO subjects (subject_code, subject_name, units, department_id)
SELECT 'IT101', 'Introduction to Information Technology', 3.0, department_id
FROM departments WHERE department_code = 'CCS';

INSERT INTO instructors (first_name, last_name, email, department_id)
SELECT 'Alex', 'Instructor', 'instructor.demo@example.test', department_id
FROM departments WHERE department_code = 'CCS';

INSERT INTO payment_methods (method_name, method_available)
VALUES ('Cash', TRUE), ('Bank transfer', TRUE);

INSERT INTO students
    (student_no, first_name, last_name, email, program_id, year_level, status)
SELECT 'SES-2025-001', 'Jamie', 'Santos', 'jamie.santos@example.test',
       program_id, 1, 'Active'
FROM programs WHERE program_code = 'BSIT';

INSERT INTO sections
    (section_code, subject_id, instructor_id, school_year, semester,
     schedule_day, start_time, end_time, room, max_capacity)
SELECT 'IT101-A', sub.subject_id, i.instructor_id, '2025-2026', '1st',
       'MWF', '08:00:00', '09:00:00', 'LAB-1', 40
FROM subjects sub
JOIN instructors i ON i.email = 'instructor.demo@example.test'
WHERE sub.subject_code = 'IT101';

INSERT INTO enrollments (student_id, section_id, status)
SELECT st.student_id, sec.section_id, 'Enrolled'
FROM students st
JOIN sections sec
WHERE st.student_no = 'SES-2025-001'
  AND sec.section_code = 'IT101-A'
  AND sec.school_year = '2025-2026'
  AND sec.semester = '1st';

-- Reporting views from the Laravel migration

CREATE VIEW v_student_details AS
SELECT s.student_id, s.student_no,
       CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS full_name,
       s.gender, s.email, s.contact_no, s.year_level, s.status,
       p.program_code, p.program_name, d.department_code
FROM students s
LEFT JOIN programs p ON p.program_id = s.program_id
LEFT JOIN departments d ON d.department_id = p.department_id;

CREATE VIEW v_section_availability AS
SELECT sec.section_id, sec.section_code, sub.subject_code, sub.subject_name, sub.units,
       CONCAT_WS(' ', i.first_name, i.last_name) AS instructor,
       sec.school_year, sec.semester, sec.schedule_day, sec.start_time, sec.end_time,
       sec.room, sec.max_capacity,
       (SELECT COUNT(*) FROM enrollments e
        WHERE e.section_id = sec.section_id AND e.status = 'Enrolled') AS enrolled_count,
       sec.max_capacity - (SELECT COUNT(*) FROM enrollments e
                           WHERE e.section_id = sec.section_id AND e.status = 'Enrolled') AS available_slots
FROM sections sec
JOIN subjects sub ON sub.subject_id = sec.subject_id
LEFT JOIN instructors i ON i.instructor_id = sec.instructor_id;

CREATE VIEW v_enrollment_details AS
SELECT e.enrollment_id, s.student_no,
       CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS student_name,
       sec.section_code, sub.subject_code, sub.subject_name, sec.school_year,
       sec.semester, e.enrollment_datetime, e.status, u.username AS processed_by
FROM enrollments e
JOIN students s ON s.student_id = e.student_id
JOIN sections sec ON sec.section_id = e.section_id
JOIN subjects sub ON sub.subject_id = sec.subject_id
LEFT JOIN users u ON u.id = e.processed_by;

CREATE VIEW v_payment_history AS
SELECT p.payment_id, st.student_no,
       CONCAT_WS(' ', st.first_name, st.middle_name, st.last_name) AS student_name,
       p.school_year, p.semester, p.amount, pm.method_name, p.payment_datetime,
       u.username AS recorded_by
FROM payments p
JOIN students st ON st.student_id = p.student_id
JOIN payment_methods pm ON pm.payment_method_id = p.payment_method_id
LEFT JOIN users u ON u.id = p.recorded_by;

CREATE VIEW v_student_term_summary AS
SELECT st.student_id, st.student_no,
       CONCAT_WS(' ', st.first_name, st.middle_name, st.last_name) AS student_name,
       sec.school_year, sec.semester, COUNT(*) AS subjects_enrolled,
       SUM(sub.units) AS total_units,
       COALESCE((SELECT SUM(pay.amount) FROM payments pay
                 WHERE pay.student_id = st.student_id
                   AND pay.school_year = sec.school_year
                   AND pay.semester = sec.semester), 0) AS total_paid
FROM enrollments e
JOIN students st ON st.student_id = e.student_id
JOIN sections sec ON sec.section_id = e.section_id
JOIN subjects sub ON sub.subject_id = sec.subject_id
WHERE e.status = 'Enrolled'
GROUP BY st.student_id, st.student_no, st.first_name, st.middle_name, st.last_name,
         sec.school_year, sec.semester;

CREATE VIEW v_user_accounts AS
SELECT id AS user_id, username, email, first_name, last_name, role, status,
       last_login, created_at
FROM users;

-- Advanced triggers, stored functions, and procedures are intentionally excluded.
