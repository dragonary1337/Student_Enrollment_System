-- =====================================================================
-- University of Saint Freya - Student Enrollment System
-- Database : student_enrollment_system_db   |   DBMS : MariaDB 10.11
-- Contents : 1 DDL  2 Triggers  3 Functions  4 Stored procedures
--            5 Views  6 Indexes  7 Sample data  8 Users, roles, privileges
--            9 Backup and restore commands
-- =====================================================================

DROP DATABASE IF EXISTS student_enrollment_system_db;
CREATE DATABASE student_enrollment_system_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_enrollment_system_db;

-- =====================================================================
-- PART 1. DDL - TABLES, KEYS AND CONSTRAINTS
-- =====================================================================

CREATE TABLE departments (
  department_id   INT AUTO_INCREMENT PRIMARY KEY,
  department_code VARCHAR(10)  NOT NULL UNIQUE,
  department_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE programs (
  program_id    INT AUTO_INCREMENT PRIMARY KEY,
  program_code  VARCHAR(10)  NOT NULL UNIQUE,
  program_name  VARCHAR(100) NOT NULL UNIQUE,
  department_id INT NOT NULL,
  CONSTRAINT fk_programs_dept FOREIGN KEY (department_id)
    REFERENCES departments (department_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE subjects (
  subject_id    INT AUTO_INCREMENT PRIMARY KEY,
  subject_code  VARCHAR(10)  NOT NULL UNIQUE,
  subject_name  VARCHAR(100) NOT NULL,
  units         DECIMAL(2,1) NOT NULL,
  department_id INT NULL,
  CONSTRAINT chk_subjects_units CHECK (units > 0),
  CONSTRAINT fk_subjects_dept FOREIGN KEY (department_id)
    REFERENCES departments (department_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE instructors (
  instructor_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name    VARCHAR(50)  NOT NULL,
  middle_name   VARCHAR(50)  NULL,
  last_name     VARCHAR(50)  NOT NULL,
  email         VARCHAR(100) NULL UNIQUE,
  department_id INT NULL,
  CONSTRAINT fk_instructors_dept FOREIGN KEY (department_id)
    REFERENCES departments (department_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE students (
  student_id      INT AUTO_INCREMENT PRIMARY KEY,
  student_no      VARCHAR(10)  NOT NULL UNIQUE,
  first_name      VARCHAR(50)  NOT NULL,
  middle_name     VARCHAR(50)  NULL,
  last_name       VARCHAR(50)  NOT NULL,
  gender          ENUM('Male','Female','Other') NULL,
  email           VARCHAR(100) NULL UNIQUE,
  contact_no      VARCHAR(20)  NULL,
  program_id      INT NULL,
  year_level      TINYINT NOT NULL,
  status          ENUM('Active','Inactive','Graduated','Dropped') NOT NULL DEFAULT 'Active',
  date_registered TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_students_year CHECK (year_level BETWEEN 1 AND 5),
  CONSTRAINT fk_students_program FOREIGN KEY (program_id)
    REFERENCES programs (program_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sections (
  section_id    INT AUTO_INCREMENT PRIMARY KEY,
  section_code  VARCHAR(10) NOT NULL,
  subject_id    INT NOT NULL,
  instructor_id INT NULL,
  school_year   VARCHAR(9)  NOT NULL,
  semester      ENUM('1st','2nd','Summer') NOT NULL,
  schedule_day  VARCHAR(10) NULL,
  start_time    TIME NULL,
  end_time      TIME NULL,
  room          VARCHAR(10) NULL,
  max_capacity  INT NOT NULL DEFAULT 40,
  CONSTRAINT uq_sections_term UNIQUE (section_code, school_year, semester),
  CONSTRAINT chk_sections_capacity CHECK (max_capacity > 0),
  CONSTRAINT chk_sections_time CHECK (end_time > start_time),
  CONSTRAINT fk_sections_subject FOREIGN KEY (subject_id)
    REFERENCES subjects (subject_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_sections_instructor FOREIGN KEY (instructor_id)
    REFERENCES instructors (instructor_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payment_methods (
  payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
  method_name       VARCHAR(50) NOT NULL UNIQUE,
  method_available  BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE users (
  user_id    INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(50)  NOT NULL UNIQUE,
  email      VARCHAR(100) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,                 -- bcrypt hash only
  first_name VARCHAR(50)  NOT NULL,
  last_name  VARCHAR(50)  NOT NULL,
  role       ENUM('Admin','Operator') NOT NULL DEFAULT 'Operator',
  status     ENUM('Pending','Active','Inactive') NOT NULL DEFAULT 'Pending',
  last_login TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE enrollments (
  enrollment_id       INT AUTO_INCREMENT PRIMARY KEY,
  student_id          INT NOT NULL,
  section_id          INT NOT NULL,
  enrollment_datetime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status              ENUM('Enrolled','Dropped','Completed') NOT NULL DEFAULT 'Enrolled',
  processed_by        INT NULL,
  CONSTRAINT uq_enrollments UNIQUE (student_id, section_id),
  CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id)
    REFERENCES students (student_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_enrollments_section FOREIGN KEY (section_id)
    REFERENCES sections (section_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_enrollments_user FOREIGN KEY (processed_by)
    REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
  payment_id        INT AUTO_INCREMENT PRIMARY KEY,
  student_id        INT NOT NULL,
  school_year       VARCHAR(9) NOT NULL,
  semester          ENUM('1st','2nd','Summer') NOT NULL,
  amount            DECIMAL(10,2) NOT NULL,
  payment_method_id INT NOT NULL,
  payment_datetime  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  recorded_by       INT NULL,
  CONSTRAINT chk_payments_amount CHECK (amount > 0),
  CONSTRAINT fk_payments_student FOREIGN KEY (student_id)
    REFERENCES students (student_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_payments_method FOREIGN KEY (payment_method_id)
    REFERENCES payment_methods (payment_method_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_payments_user FOREIGN KEY (recorded_by)
    REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- PART 2. TRIGGERS - business rules enforced inside the database
-- =====================================================================
DELIMITER $$

-- BR-10 / capacity: only Active students; a section cannot exceed max_capacity
CREATE TRIGGER trg_enrollments_before_insert
BEFORE INSERT ON enrollments FOR EACH ROW
BEGIN
  DECLARE v_student_status VARCHAR(20);
  DECLARE v_capacity INT;
  DECLARE v_enrolled INT;

  SELECT status INTO v_student_status FROM students WHERE student_id = NEW.student_id;
  IF v_student_status <> 'Active' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only Active students can be enrolled.';
  END IF;

  IF NEW.status = 'Enrolled' THEN
    SELECT max_capacity INTO v_capacity FROM sections WHERE section_id = NEW.section_id;
    SELECT COUNT(*) INTO v_enrolled FROM enrollments
     WHERE section_id = NEW.section_id AND status = 'Enrolled';
    IF v_enrolled >= v_capacity THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The section is already full.';
    END IF;
  END IF;
END$$

-- Same capacity check when a Dropped record is set back to Enrolled
CREATE TRIGGER trg_enrollments_before_update
BEFORE UPDATE ON enrollments FOR EACH ROW
BEGIN
  DECLARE v_capacity INT;
  DECLARE v_enrolled INT;
  IF NEW.status = 'Enrolled' AND OLD.status <> 'Enrolled' THEN
    SELECT max_capacity INTO v_capacity FROM sections WHERE section_id = NEW.section_id;
    SELECT COUNT(*) INTO v_enrolled FROM enrollments
     WHERE section_id = NEW.section_id AND status = 'Enrolled';
    IF v_enrolled >= v_capacity THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The section is already full.';
    END IF;
  END IF;
END$$

-- BR-14: a payment must use an available payment method
CREATE TRIGGER trg_payments_before_insert
BEFORE INSERT ON payments FOR EACH ROW
BEGIN
  IF (SELECT method_available FROM payment_methods
       WHERE payment_method_id = NEW.payment_method_id) = FALSE THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The selected payment method is not available.';
  END IF;
END$$

-- Keep at least one Active Admin so the system can always be managed
CREATE TRIGGER trg_users_before_update
BEFORE UPDATE ON users FOR EACH ROW
BEGIN
  IF OLD.role = 'Admin' AND OLD.status = 'Active'
     AND (NEW.role <> 'Admin' OR NEW.status <> 'Active')
     AND (SELECT COUNT(*) FROM users WHERE role = 'Admin' AND status = 'Active') <= 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The last Active Admin cannot be deactivated or demoted.';
  END IF;
END$$

CREATE TRIGGER trg_users_before_delete
BEFORE DELETE ON users FOR EACH ROW
BEGIN
  IF OLD.role = 'Admin' AND OLD.status = 'Active'
     AND (SELECT COUNT(*) FROM users WHERE role = 'Admin' AND status = 'Active') <= 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The last Active Admin cannot be deleted.';
  END IF;
END$$

-- =====================================================================
-- PART 3. STORED FUNCTIONS
-- =====================================================================

-- Remaining slots in a section
CREATE FUNCTION fn_available_slots(p_section_id INT)
RETURNS INT
READS SQL DATA
BEGIN
  RETURN (SELECT s.max_capacity -
                 (SELECT COUNT(*) FROM enrollments e
                   WHERE e.section_id = s.section_id AND e.status = 'Enrolled')
            FROM sections s WHERE s.section_id = p_section_id);
END$$

-- Total enrolled units of a student in a term
CREATE FUNCTION fn_total_units(p_student_id INT, p_school_year VARCHAR(9), p_semester VARCHAR(6))
RETURNS DECIMAL(5,1)
READS SQL DATA
BEGIN
  RETURN (SELECT IFNULL(SUM(sub.units), 0)
            FROM enrollments e
            JOIN sections sec ON sec.section_id = e.section_id
            JOIN subjects sub ON sub.subject_id = sec.subject_id
           WHERE e.student_id = p_student_id AND e.status = 'Enrolled'
             AND sec.school_year = p_school_year AND sec.semester = p_semester);
END$$

-- Total amount paid by a student in a term
CREATE FUNCTION fn_total_paid(p_student_id INT, p_school_year VARCHAR(9), p_semester VARCHAR(6))
RETURNS DECIMAL(10,2)
READS SQL DATA
BEGIN
  RETURN (SELECT IFNULL(SUM(amount), 0) FROM payments
           WHERE student_id = p_student_id
             AND school_year = p_school_year AND semester = p_semester);
END$$

-- =====================================================================
-- PART 4. STORED PROCEDURES
-- Transactional processes (START TRANSACTION ... COMMIT / ROLLBACK):
--   TP-1 sp_enroll_student   - enroll a student in a section
--   TP-2 sp_drop_add         - drop one section and add another in one step
--   TP-3 sp_record_payment   - record a payment for an enrolled term
-- Account procedures: sp_register_user, sp_activate_user, sp_record_login
-- =====================================================================

-- TP-1
CREATE PROCEDURE sp_enroll_student(IN p_student_id INT, IN p_section_id INT, IN p_user_id INT)
BEGIN
  DECLARE v_user_status VARCHAR(20) DEFAULT NULL;
  DECLARE v_section INT DEFAULT NULL;
  DECLARE v_existing INT DEFAULT NULL;
  DECLARE v_existing_status VARCHAR(20) DEFAULT NULL;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SELECT status INTO v_user_status FROM users WHERE user_id = p_user_id;
  IF v_user_status IS NULL OR v_user_status <> 'Active' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only an Active Admin or Operator can record enrollments.';
  END IF;

  -- lock the section row so two operators cannot fill the last slot at once
  SELECT section_id INTO v_section FROM sections WHERE section_id = p_section_id FOR UPDATE;
  IF v_section IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Section not found.';
  END IF;

  SELECT enrollment_id, status INTO v_existing, v_existing_status
    FROM enrollments WHERE student_id = p_student_id AND section_id = p_section_id;

  IF v_existing IS NULL THEN
    INSERT INTO enrollments (student_id, section_id, processed_by)
    VALUES (p_student_id, p_section_id, p_user_id);          -- triggers check status and capacity
  ELSEIF v_existing_status = 'Enrolled' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The student is already enrolled in this section.';
  ELSE
    UPDATE enrollments
       SET status = 'Enrolled', enrollment_datetime = NOW(), processed_by = p_user_id
     WHERE enrollment_id = v_existing;
  END IF;

  COMMIT;
END$$

-- TP-2
CREATE PROCEDURE sp_drop_add(IN p_drop_enrollment_id INT, IN p_add_section_id INT, IN p_user_id INT)
BEGIN
  DECLARE v_user_status VARCHAR(20) DEFAULT NULL;
  DECLARE v_student INT DEFAULT NULL;
  DECLARE v_old_section INT DEFAULT NULL;
  DECLARE v_old_status VARCHAR(20) DEFAULT NULL;
  DECLARE v_section INT DEFAULT NULL;
  DECLARE v_existing INT DEFAULT NULL;
  DECLARE v_existing_status VARCHAR(20) DEFAULT NULL;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SELECT status INTO v_user_status FROM users WHERE user_id = p_user_id;
  IF v_user_status IS NULL OR v_user_status <> 'Active' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only an Active Admin or Operator can process drop/add.';
  END IF;

  SELECT student_id, section_id, status INTO v_student, v_old_section, v_old_status
    FROM enrollments WHERE enrollment_id = p_drop_enrollment_id FOR UPDATE;
  IF v_student IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Enrollment record not found.';
  END IF;
  IF v_old_status <> 'Enrolled' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only an Enrolled record can be dropped.';
  END IF;
  IF v_old_section = p_add_section_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The new section must be different from the dropped section.';
  END IF;

  SELECT section_id INTO v_section FROM sections WHERE section_id = p_add_section_id FOR UPDATE;
  IF v_section IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Section to add not found.';
  END IF;

  -- step 1: drop
  UPDATE enrollments SET status = 'Dropped', processed_by = p_user_id
   WHERE enrollment_id = p_drop_enrollment_id;

  -- step 2: add (if this fails, step 1 is rolled back as well)
  SELECT enrollment_id, status INTO v_existing, v_existing_status
    FROM enrollments WHERE student_id = v_student AND section_id = p_add_section_id;

  IF v_existing IS NULL THEN
    INSERT INTO enrollments (student_id, section_id, processed_by)
    VALUES (v_student, p_add_section_id, p_user_id);
  ELSEIF v_existing_status = 'Enrolled' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The student is already enrolled in the new section.';
  ELSE
    UPDATE enrollments
       SET status = 'Enrolled', enrollment_datetime = NOW(), processed_by = p_user_id
     WHERE enrollment_id = v_existing;
  END IF;

  COMMIT;
END$$

-- TP-3
CREATE PROCEDURE sp_record_payment(
  IN p_student_id INT, IN p_school_year VARCHAR(9), IN p_semester VARCHAR(6),
  IN p_amount DECIMAL(10,2), IN p_payment_method_id INT, IN p_user_id INT,
  OUT p_payment_id INT)
BEGIN
  DECLARE v_user_status VARCHAR(20) DEFAULT NULL;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SELECT status INTO v_user_status FROM users WHERE user_id = p_user_id;
  IF v_user_status IS NULL OR v_user_status <> 'Active' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only an Active Admin or Operator can record payments.';
  END IF;

  -- subquery: the student must be enrolled in at least one section of that term
  IF NOT EXISTS (SELECT 1 FROM enrollments e
                   JOIN sections s ON s.section_id = e.section_id
                  WHERE e.student_id = p_student_id AND e.status = 'Enrolled'
                    AND s.school_year = p_school_year AND s.semester = p_semester) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The student has no enrollment for this term.';
  END IF;

  INSERT INTO payments (student_id, school_year, semester, amount, payment_method_id, recorded_by)
  VALUES (p_student_id, p_school_year, p_semester, p_amount, p_payment_method_id, p_user_id);
  SET p_payment_id = LAST_INSERT_ID();

  COMMIT;
END$$

-- Registration (FR-12): password is hashed by the application (Laravel Hash::make)
CREATE PROCEDURE sp_register_user(
  IN p_username VARCHAR(50), IN p_email VARCHAR(100), IN p_password_hash VARCHAR(255),
  IN p_first_name VARCHAR(50), IN p_last_name VARCHAR(50))
BEGIN
  INSERT INTO users (username, email, password, first_name, last_name)
  VALUES (p_username, p_email, p_password_hash, p_first_name, p_last_name);   -- defaults: Operator, Pending
END$$

-- Account management (FR-14): only an Active Admin can activate an account or set its role
CREATE PROCEDURE sp_activate_user(IN p_admin_id INT, IN p_user_id INT, IN p_role VARCHAR(10))
BEGIN
  IF NOT EXISTS (SELECT 1 FROM users
                  WHERE user_id = p_admin_id AND role = 'Admin' AND status = 'Active') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only an Active Admin can activate accounts.';
  END IF;
  UPDATE users SET status = 'Active', role = p_role WHERE user_id = p_user_id;
END$$

-- Login (FR-13): called by the application after the password hash is verified
CREATE PROCEDURE sp_record_login(IN p_user_id INT)
BEGIN
  UPDATE users SET last_login = NOW() WHERE user_id = p_user_id AND status = 'Active';
END$$

DELIMITER ;

-- =====================================================================
-- PART 5. VIEWS
-- =====================================================================

CREATE VIEW v_student_details AS
SELECT s.student_id, s.student_no,
       CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS full_name,
       s.gender, s.email, s.contact_no, s.year_level, s.status,
       p.program_code, p.program_name, d.department_code
  FROM students s
  LEFT JOIN programs p    ON p.program_id = s.program_id
  LEFT JOIN departments d ON d.department_id = p.department_id;

CREATE VIEW v_section_availability AS
SELECT sec.section_id, sec.section_code, sub.subject_code, sub.subject_name, sub.units,
       CONCAT_WS(' ', i.first_name, i.last_name) AS instructor,
       sec.school_year, sec.semester, sec.schedule_day, sec.start_time, sec.end_time, sec.room,
       sec.max_capacity,
       (SELECT COUNT(*) FROM enrollments e
         WHERE e.section_id = sec.section_id AND e.status = 'Enrolled') AS enrolled_count,
       fn_available_slots(sec.section_id) AS available_slots
  FROM sections sec
  JOIN subjects sub      ON sub.subject_id = sec.subject_id
  LEFT JOIN instructors i ON i.instructor_id = sec.instructor_id;

CREATE VIEW v_enrollment_details AS
SELECT e.enrollment_id, st.student_no,
       CONCAT(st.first_name, ' ', st.last_name) AS student_name,
       sub.subject_code, sub.subject_name, sec.section_code,
       sec.school_year, sec.semester, e.status, e.enrollment_datetime,
       u.username AS processed_by
  FROM enrollments e
  JOIN students st  ON st.student_id = e.student_id
  JOIN sections sec ON sec.section_id = e.section_id
  JOIN subjects sub ON sub.subject_id = sec.subject_id
  LEFT JOIN users u ON u.user_id = e.processed_by;

CREATE VIEW v_payment_history AS
SELECT p.payment_id, st.student_no,
       CONCAT(st.first_name, ' ', st.last_name) AS student_name,
       p.school_year, p.semester, p.amount, pm.method_name, p.payment_datetime,
       u.username AS recorded_by
  FROM payments p
  JOIN students st        ON st.student_id = p.student_id
  JOIN payment_methods pm ON pm.payment_method_id = p.payment_method_id
  LEFT JOIN users u       ON u.user_id = p.recorded_by;

CREATE VIEW v_student_term_summary AS
SELECT st.student_id, st.student_no,
       CONCAT(st.first_name, ' ', st.last_name) AS student_name,
       sec.school_year, sec.semester,
       COUNT(*)       AS subjects_enrolled,
       SUM(sub.units) AS total_units,
       fn_total_paid(st.student_id, sec.school_year, sec.semester) AS total_paid
  FROM enrollments e
  JOIN students st  ON st.student_id = e.student_id
  JOIN sections sec ON sec.section_id = e.section_id
  JOIN subjects sub ON sub.subject_id = sec.subject_id
 WHERE e.status = 'Enrolled'
 GROUP BY st.student_id, st.student_no, st.first_name, st.last_name, sec.school_year, sec.semester;

-- account list without password hashes
CREATE VIEW v_user_accounts AS
SELECT user_id, username, email, CONCAT(first_name, ' ', last_name) AS full_name,
       role, status, last_login, created_at
  FROM users;

-- =====================================================================
-- PART 6. INDEXES (query optimization; PK, UNIQUE and FK columns are already indexed)
-- =====================================================================
CREATE INDEX idx_students_name              ON students (last_name, first_name);
CREATE INDEX idx_students_status            ON students (status);
CREATE INDEX idx_sections_term              ON sections (school_year, semester);
CREATE INDEX idx_enrollments_section_status ON enrollments (section_id, status);
CREATE INDEX idx_payments_student_term      ON payments (student_id, school_year, semester);
CREATE INDEX idx_users_role_status          ON users (role, status);

-- =====================================================================
-- PART 7. SAMPLE DATA (42 records) - inserted after the triggers, so every row passes them
-- =====================================================================

INSERT INTO departments (department_id, department_code, department_name) VALUES
(1, 'CCE',  'College of Computing Education'),
(2, 'CBAE', 'College of Business Administration Education'),
(3, 'CAS',  'College of Arts and Sciences');

INSERT INTO programs (program_id, program_code, program_name, department_id) VALUES
(1, 'BSIT', 'Bachelor of Science in Information Technology', 1),
(2, 'BSCS', 'Bachelor of Science in Computer Science', 1),
(3, 'BSBA', 'Bachelor of Science in Business Administration', 2);

INSERT INTO subjects (subject_id, subject_code, subject_name, units, department_id) VALUES
(1, 'IT6', 'Fundamentals of Database', 3.0, 1),
(2, 'IT7', 'Web Systems Development',  3.0, 1),
(3, 'GE1', 'Purposive Communication',  3.0, 3),
(4, 'BA1', 'Principles of Management', 3.0, 2);

INSERT INTO instructors (instructor_id, first_name, middle_name, last_name, email, department_id) VALUES
(1, 'Maria', 'Lopez',  'Santos', 'msantos@usf.edu.ph', 1),
(2, 'Jose',  NULL,     'Reyes',  'jreyes@usf.edu.ph',  1),
(3, 'Ana',   'Garcia', 'Lim',    'alim@usf.edu.ph',    3);

INSERT INTO students (student_id, student_no, first_name, middle_name, last_name, gender, email, contact_no, program_id, year_level, status) VALUES
(1, '2025-0001', 'Carlo', 'Dela', 'Cruz',       'Male',   'carlo.cruz@usf.edu.ph',     '09171234501', 1, 2, 'Active'),
(2, '2025-0002', 'Bea',   NULL,   'Villanueva', 'Female', 'bea.villanueva@usf.edu.ph', '09171234502', 1, 2, 'Active'),
(3, '2025-0003', 'Mark',  'Tan',  'Gonzales',   'Male',   'mark.gonzales@usf.edu.ph',  '09171234503', 2, 1, 'Active'),
(4, '2025-0004', 'Lea',   NULL,   'Ramos',      'Female', 'lea.ramos@usf.edu.ph',      '09171234504', 3, 1, 'Active'),
(5, '2025-0005', 'Paolo', 'Diaz', 'Mendoza',    'Male',   NULL,                        '09171234505', 1, 3, 'Inactive');

-- GE1-A has max_capacity 3 on purpose so the "section full" rollback can be demonstrated
INSERT INTO sections (section_id, section_code, subject_id, instructor_id, school_year, semester, schedule_day, start_time, end_time, room, max_capacity) VALUES
(1, 'IT6-A', 1, 1,    '2026-2027', '1st', 'MWF', '08:00:00', '09:00:00', 'R301', 40),
(2, 'IT7-A', 2, 2,    '2026-2027', '1st', 'TTh', '10:00:00', '11:30:00', 'R302', 35),
(3, 'GE1-A', 3, 3,    '2026-2027', '1st', 'MWF', '13:00:00', '14:00:00', 'R201', 3),
(4, 'BA1-A', 4, NULL, '2026-2027', '1st', 'TTh', '14:30:00', '16:00:00', 'R105', 40);

INSERT INTO payment_methods (payment_method_id, method_name, method_available) VALUES
(1, 'Cash',          TRUE),
(2, 'GCash',         TRUE),
(3, 'Bank Transfer', FALSE);

-- password for all sample accounts = "password" (bcrypt); change after first login
INSERT INTO users (user_id, username, email, password, first_name, last_name, role, status, last_login) VALUES
(1, 'admin',     'admin@usf.edu.ph',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'Admin',    'Active',  '2026-08-01 08:00:00'),
(2, 'registrar', 'registrar@usf.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rosa',   'Navarro',       'Operator', 'Active',  '2026-08-03 09:15:00'),
(3, 'finance01', 'finance01@usf.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ramon',  'Aquino',        'Operator', 'Pending', NULL);

INSERT INTO enrollments (enrollment_id, student_id, section_id, enrollment_datetime, status, processed_by) VALUES
(1, 1, 1, '2026-08-03 09:30:00', 'Enrolled', 2),
(2, 1, 2, '2026-08-03 09:31:00', 'Enrolled', 2),
(3, 1, 3, '2026-08-03 09:32:00', 'Enrolled', 2),
(4, 2, 1, '2026-08-03 10:05:00', 'Enrolled', 2),
(5, 2, 2, '2026-08-03 10:06:00', 'Dropped',  2),
(6, 3, 1, '2026-08-04 08:45:00', 'Enrolled', 2),
(7, 3, 3, '2026-08-04 08:46:00', 'Enrolled', 2),
(8, 4, 4, '2026-08-04 13:20:00', 'Enrolled', 1),
(9, 4, 3, '2026-08-04 13:21:00', 'Enrolled', 1);

INSERT INTO payments (payment_id, student_id, school_year, semester, amount, payment_method_id, payment_datetime, recorded_by) VALUES
(1, 1, '2026-2027', '1st', 5000.00, 1, '2026-08-03 11:00:00', 2),
(2, 2, '2026-2027', '1st', 3500.00, 2, '2026-08-03 11:20:00', 2),
(3, 3, '2026-2027', '1st', 4000.00, 1, '2026-08-04 10:00:00', 2),
(4, 4, '2026-2027', '1st', 6000.00, 2, '2026-08-04 14:00:00', 1),
(5, 1, '2026-2027', '1st', 2500.00, 2, '2026-09-01 15:30:00', 2);

-- =====================================================================
-- PART 8. DATABASE ADMINISTRATION - ROLES, USERS AND PRIVILEGES
-- (application-level roles are in the users table; these are database-level accounts)
-- =====================================================================

DROP USER IF EXISTS 'usf_dba'@'localhost', 'usf_app'@'localhost',
                    'usf_registrar'@'localhost', 'usf_viewer'@'localhost';
DROP ROLE IF EXISTS role_admin, role_operator, role_viewer;

CREATE ROLE role_admin;
CREATE ROLE role_operator;
CREATE ROLE role_viewer;

-- role_admin: full control of student_enrollment_system_db only
GRANT ALL PRIVILEGES ON student_enrollment_system_db.* TO role_admin;

-- role_operator: read master data, write students/enrollments/payments, run the transactions
GRANT SELECT ON student_enrollment_system_db.departments     TO role_operator;
GRANT SELECT ON student_enrollment_system_db.programs        TO role_operator;
GRANT SELECT ON student_enrollment_system_db.subjects        TO role_operator;
GRANT SELECT ON student_enrollment_system_db.instructors     TO role_operator;
GRANT SELECT ON student_enrollment_system_db.sections        TO role_operator;
GRANT SELECT ON student_enrollment_system_db.payment_methods TO role_operator;
GRANT SELECT, INSERT, UPDATE ON student_enrollment_system_db.students    TO role_operator;
GRANT SELECT, INSERT, UPDATE ON student_enrollment_system_db.enrollments TO role_operator;
GRANT SELECT, INSERT         ON student_enrollment_system_db.payments    TO role_operator;
GRANT SELECT ON student_enrollment_system_db.v_section_availability TO role_operator;
GRANT SELECT ON student_enrollment_system_db.v_enrollment_details   TO role_operator;
GRANT SELECT ON student_enrollment_system_db.v_payment_history      TO role_operator;
GRANT SELECT ON student_enrollment_system_db.v_student_term_summary TO role_operator;
GRANT EXECUTE ON PROCEDURE student_enrollment_system_db.sp_enroll_student TO role_operator;
GRANT EXECUTE ON PROCEDURE student_enrollment_system_db.sp_drop_add       TO role_operator;
GRANT EXECUTE ON PROCEDURE student_enrollment_system_db.sp_record_payment TO role_operator;

-- role_viewer: reports only (views), no access to base tables or password hashes
GRANT SELECT ON student_enrollment_system_db.v_student_details      TO role_viewer;
GRANT SELECT ON student_enrollment_system_db.v_section_availability TO role_viewer;
GRANT SELECT ON student_enrollment_system_db.v_enrollment_details   TO role_viewer;
GRANT SELECT ON student_enrollment_system_db.v_payment_history      TO role_viewer;
GRANT SELECT ON student_enrollment_system_db.v_student_term_summary TO role_viewer;

-- database accounts (replace the passwords before submission)
CREATE USER 'usf_dba'@'localhost'       IDENTIFIED BY 'CHANGE_ME_BEFORE_USE';
CREATE USER 'usf_registrar'@'localhost' IDENTIFIED BY 'CHANGE_ME_BEFORE_USE';
CREATE USER 'usf_viewer'@'localhost'    IDENTIFIED BY 'CHANGE_ME_BEFORE_USE';
CREATE USER 'usf_app'@'localhost'       IDENTIFIED BY 'CHANGE_ME_BEFORE_USE';

GRANT role_admin    TO 'usf_dba'@'localhost';
GRANT role_operator TO 'usf_registrar'@'localhost';
GRANT role_viewer   TO 'usf_viewer'@'localhost';
SET DEFAULT ROLE role_admin    FOR 'usf_dba'@'localhost';
SET DEFAULT ROLE role_operator FOR 'usf_registrar'@'localhost';
SET DEFAULT ROLE role_viewer   FOR 'usf_viewer'@'localhost';

-- the Laravel application connects as usf_app: data access only, no DROP/ALTER/GRANT
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON student_enrollment_system_db.* TO 'usf_app'@'localhost';

-- =====================================================================
-- PART 9. BACKUP AND RESTORATION (run in the command prompt, not in SQL)
-- =====================================================================
-- Backup (tables, data, routines and triggers):
--   mariadb-dump -u root -p --routines --triggers --databases student_enrollment_system_db > enrollment_db_backup.sql
-- Restore:
--   mariadb -u root -p < enrollment_db_backup.sql
-- (mysqldump / mysql work the same way. In phpMyAdmin: Export tab = backup, Import tab = restore.)
-- Note: users and roles are stored in the mysql system database; re-run PART 8 after restoring on a new server.