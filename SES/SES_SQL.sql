CREATE DATABASE IF NOT EXISTS student_enrollments_system;
USE student_enrollments_system;


CREATE TABLE `departments` (
  `department_id` int PRIMARY KEY AUTO_INCREMENT,
  `department_code` varchar(10) UNIQUE NOT NULL,
  `department_name` varchar(100) UNIQUE NOT NULL
);

CREATE TABLE `programs` (
  `program_id` int PRIMARY KEY AUTO_INCREMENT,
  `program_code` varchar(10) UNIQUE NOT NULL,
  `program_name` varchar(100) UNIQUE NOT NULL,
  `department_id` int NOT NULL
);

CREATE TABLE `instructors` (
  `instructor_id` int PRIMARY KEY AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50),
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) UNIQUE,
  `department_id` int
);

CREATE TABLE `subjects` (
  `subject_id` int PRIMARY KEY AUTO_INCREMENT,
  `subject_code` varchar(10) UNIQUE NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `units` decimal(2,1) NOT NULL DEFAULT 3,
  `department_id` int
);

CREATE TABLE `students` (
  `student_id` int PRIMARY KEY AUTO_INCREMENT,
  `student_no` varchar(10) UNIQUE NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50),
  `last_name` varchar(50) NOT NULL,
  `gender` ENUM ('male', 'female', 'other', 'prefer_not_to_say'),
  `email` varchar(100) UNIQUE,
  `contact_no` varchar(20),
  `program_id` int,
  `year_level` tinyint NOT NULL DEFAULT 1 COMMENT '1 to 5',
  `status` ENUM ('active', 'inactive', 'graduated', 'dropped') NOT NULL DEFAULT 'active',
  `date_registered` timestamp DEFAULT (now())
);

CREATE TABLE `sections` (
  `section_id` int PRIMARY KEY AUTO_INCREMENT,
  `section_code` varchar(10) NOT NULL,
  `subject_id` int NOT NULL,
  `instructor_id` int,
  `school_year` varchar(9) NOT NULL COMMENT 'Format: YYYY-YYYY',
  `semester` ENUM ('1st', '2nd') NOT NULL DEFAULT '1st',
  `schedule_day` varchar(10) COMMENT 'e.g. MWF, TTh',
  `start_time` time COMMENT 'Displayed as HH:MM',
  `end_time` time COMMENT 'Displayed as HH:MM',
  `room` varchar(10),
  `max_capacity` int NOT NULL DEFAULT 40
);

CREATE TABLE `enrollments` (
  `enrollment_id` int PRIMARY KEY AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `section_id` int NOT NULL,
  `enrollment_datetime` timestamp DEFAULT (now()),
  `status` ENUM ('enrolled', 'dropped', 'completed') NOT NULL DEFAULT 'enrolled'
);

CREATE TABLE `payment_methods` (
  `payment_method_id` int PRIMARY KEY AUTO_INCREMENT,
  `method_name` varchar(50) UNIQUE NOT NULL,
  `method_available` boolean NOT NULL DEFAULT true
);

CREATE TABLE `payments` (
  `payment_id` int PRIMARY KEY AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `school_year` varchar(9) NOT NULL COMMENT 'Format: YYYY-YYYY',
  `semester` ENUM ('1st', '2nd') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method_id` int NOT NULL,
  `payment_datetime` timestamp DEFAULT (now())
);

CREATE UNIQUE INDEX `sections_index_0` ON `sections` (`section_code`, `school_year`, `semester`);

CREATE UNIQUE INDEX `enrollments_index_1` ON `enrollments` (`student_id`, `section_id`);

ALTER TABLE `programs` ADD FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

ALTER TABLE `instructors` ADD FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

ALTER TABLE `subjects` ADD FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

ALTER TABLE `students` ADD FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

ALTER TABLE `sections` ADD FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

ALTER TABLE `sections` ADD FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`);

ALTER TABLE `enrollments` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

ALTER TABLE `enrollments` ADD FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

SET NAMES utf8mb4;
START TRANSACTION;
 
-- 5 departments
INSERT INTO `departments` (`department_id`, `department_code`, `department_name`) VALUES
  (1, 'CCE', 'College of Computing Education'),
  (2, 'CEE', 'College of Engineering Education'),
  (3, 'CBAE', 'College of Business Administration Education'),
  (4, 'CASE', 'College of Arts and Sciences Education'),
  (5, 'CAE', 'College of Accounting Education');
 
-- 8 programs
INSERT INTO `programs` (`program_id`, `program_code`, `program_name`, `department_id`) VALUES
  (1, 'BSIT', 'Bachelor of Science in Information Technology', 1),
  (2, 'BSCS', 'Bachelor of Science in Computer Science', 1),
  (3, 'BSIS', 'Bachelor of Science in Information Systems', 1),
  (4, 'BSCE', 'Bachelor of Science in Civil Engineering', 2),
  (5, 'BSEE', 'Bachelor of Science in Electrical Engineering', 2),
  (6, 'BSBA-FM', 'BS Business Administration major in Financial Management', 3),
  (7, 'BSBA-MM', 'BS Business Administration major in Marketing Management', 3),
  (8, 'BSA', 'Bachelor of Science in Accountancy', 5);
 
-- 10 instructors (id 10 = part-time, no department yet -> shows NULL in LEFT JOIN)
INSERT INTO `instructors` (`instructor_id`, `first_name`, `middle_name`, `last_name`, `email`, `department_id`) VALUES
  (1, 'Maria', 'Lopez', 'Santos', 'msantos@umindanao.edu.ph', 1),
  (2, 'Ramon', NULL, 'Dela Cruz', 'rdelacruz@umindanao.edu.ph', 1),
  (3, 'Cristina', 'Velasco', 'Torres', 'ctorres@umindanao.edu.ph', 1),
  (4, 'Arnel', 'Bello', 'Fernandez', 'afernandez@umindanao.edu.ph', 1),
  (5, 'Grace', NULL, 'Lacson', 'glacson@umindanao.edu.ph', 1),
  (6, 'Dennis', 'Ocampo', 'Garcia', 'dgarcia@umindanao.edu.ph', 1),
  (7, 'Joel', NULL, 'Villanueva', 'jvillanueva@umindanao.edu.ph', 2),
  (8, 'Rowena', 'Diaz', 'Salazar', 'rsalazar@umindanao.edu.ph', 3),
  (9, 'Liza', 'Abad', 'Peñaranda', 'lpenaranda@umindanao.edu.ph', 4),
  (10, 'Noel', NULL, 'Cabrera', NULL, NULL);
 
-- 17 subjects (IT9 has no section yet -> subject never offered)
INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `units`, `department_id`) VALUES
  (1, 'CC101', 'Introduction to Computing', 3.0, 1),
  (2, 'CC102', 'Computer Programming 1', 3.0, 1),
  (3, 'CC103', 'Computer Programming 2', 3.0, 1),
  (4, 'CC104', 'Data Structures and Algorithms', 3.0, 1),
  (5, 'IT6', 'Fundamentals of Database Systems', 3.0, 1),
  (6, 'IT6L', 'Fundamentals of Database Systems (Laboratory)', 1.0, 1),
  (7, 'IT7', 'Human Computer Interaction', 3.0, 1),
  (8, 'IT8', 'Networking 1', 3.0, 1),
  (9, 'CS3', 'Discrete Structures', 3.0, 1),
  (10, 'GE4', 'Mathematics in the Modern World', 3.0, 4),
  (11, 'GE5', 'Purposive Communication', 3.0, 4),
  (12, 'GE6', 'Art Appreciation', 3.0, 4),
  (13, 'PE3', 'Physical Activities Towards Health and Fitness 1', 2.0, 4),
  (14, 'MATH21', 'Engineering Calculus 1', 4.0, 2),
  (15, 'BA101', 'Principles of Management', 3.0, 3),
  (16, 'ACC101', 'Financial Accounting and Reporting', 3.0, 5),
  (17, 'IT9', 'Web Systems and Technologies', 3.0, 1);
 
-- 6 payment methods (Check is no longer accepted)
INSERT INTO `payment_methods` (`payment_method_id`, `method_name`, `method_available`) VALUES
  (1, 'Cash', TRUE),
  (2, 'GCash', TRUE),
  (3, 'Maya', TRUE),
  (4, 'Bank Transfer', TRUE),
  (5, 'Credit/Debit Card', TRUE),
  (6, 'Check', FALSE);
 
-- 21 sections: 1-5 = 2025-2026 2nd sem, 6-21 = 2026-2027 1st sem
-- 3440 is full (5/5), 3448 has no instructor (TBA), 3453 has no enrollees
INSERT INTO `sections` (`section_id`, `section_code`, `subject_id`, `instructor_id`, `school_year`, `semester`, `schedule_day`, `start_time`, `end_time`, `room`, `max_capacity`) VALUES
  (1, '2201', 3, 1, '2025-2026', '2nd', 'MWF', '08:00:00', '09:00:00', 'GET 301', 40),
  (2, '2202', 3, 2, '2025-2026', '2nd', 'TTh', '13:00:00', '14:30:00', 'GET 302', 40),
  (3, '2203', 10, 9, '2025-2026', '2nd', 'MWF', '10:00:00', '11:00:00', 'BE 210', 45),
  (4, '2204', 11, 9, '2025-2026', '2nd', 'TTh', '08:00:00', '09:30:00', 'BE 211', 45),
  (5, '2205', 9, 3, '2025-2026', '2nd', 'TTh', '10:00:00', '11:30:00', 'GET 303', 40),
  (6, '3438', 5, 1, '2026-2027', '1st', 'MW', '08:00:00', '09:30:00', 'GET 301', 40),
  (7, '3439', 6, 1, '2026-2027', '1st', 'F', '08:00:00', '11:00:00', 'CL 4', 40),
  (8, '3440', 5, 2, '2026-2027', '1st', 'TTh', '13:00:00', '14:30:00', 'GET 302', 5),
  (9, '3441', 7, 4, '2026-2027', '1st', 'TTh', '10:00:00', '11:30:00', 'GET 303', 40),
  (10, '3442', 4, 3, '2026-2027', '1st', 'MW', '13:00:00', '14:30:00', 'GET 304', 40),
  (11, '3443', 12, 9, '2026-2027', '1st', 'S', '08:00:00', '11:00:00', 'BE 210', 45),
  (12, '3444', 13, 10, '2026-2027', '1st', 'F', '15:00:00', '17:00:00', 'GYM', 40),
  (13, '3445', 1, 5, '2026-2027', '1st', 'MWF', '08:00:00', '09:00:00', 'GET 305', 40),
  (14, '3446', 2, 6, '2026-2027', '1st', 'MWF', '09:00:00', '10:00:00', 'CL 2', 40),
  (15, '3447', 10, 9, '2026-2027', '1st', 'TTh', '08:00:00', '09:30:00', 'BE 211', 45),
  (16, '3448', 11, NULL, '2026-2027', '1st', 'MW', '15:00:00', '16:30:00', 'BE 212', 45),
  (17, '3449', 8, 4, '2026-2027', '1st', 'MW', '10:00:00', '11:30:00', 'CL 3', 35),
  (18, '3450', 14, 7, '2026-2027', '1st', 'MWF', '13:00:00', '14:00:00', 'BE 105', 40),
  (19, '3451', 15, 8, '2026-2027', '1st', 'TTh', '15:00:00', '16:30:00', 'BE 301', 45),
  (20, '3452', 16, 8, '2026-2027', '1st', 'MWF', '10:00:00', '11:00:00', 'BE 302', 40),
  (21, '3453', 9, 3, '2026-2027', '1st', 'S', '13:00:00', '16:00:00', 'GET 303', 40);
 
-- 30 students: 28 graduated, 29 inactive, 30 dropped out
INSERT INTO `students` (`student_id`, `student_no`, `first_name`, `middle_name`, `last_name`, `gender`, `email`, `contact_no`, `program_id`, `year_level`, `status`, `date_registered`) VALUES
  (1, '2025-00001', 'Andrea', 'Reyes', 'Castillo', 'female', 'andrea.castillo@gmail.com', '09271747214', 1, 2, 'active', '2025-06-26 09:15:00'),
  (2, '2025-00002', 'Mark', NULL, 'Bautista', 'male', 'mark.bautista@gmail.com', '09396517563', 1, 2, 'active', '2025-06-10 09:32:00'),
  (3, '2025-00003', 'Joanna', 'Lim', 'Muñoz', 'female', 'joanna.munoz@gmail.com', '09273926464', 1, 2, 'active', '2025-06-21 10:14:00'),
  (4, '2025-00004', 'Carlo', 'Santos', 'Gonzales', 'male', 'carlo.gonzales@gmail.com', NULL, 1, 2, 'active', '2025-06-10 11:59:00'),
  (5, '2025-00005', 'Bea', 'Tan', 'Mercado', 'female', 'bea.mercado@gmail.com', '09775186334', 1, 2, 'active', '2025-06-21 12:39:00'),
  (6, '2025-00006', 'Paolo', NULL, 'Ramos', 'male', NULL, '09276926486', 1, 2, 'active', '2025-06-06 11:51:00'),
  (7, '2025-00007', 'Kimberly', 'Uy', 'Aquino', 'female', 'kimberly.aquino@gmail.com', '09663891750', 1, 2, 'active', '2025-06-24 16:22:00'),
  (8, '2025-00008', 'Rafael', 'Cruz', 'Navarro', 'male', 'rafael.navarro@gmail.com', '09397635282', 1, 2, 'active', '2025-06-22 08:15:00'),
  (9, '2025-00009', 'Jerome', NULL, 'Villareal', 'male', 'jerome.villareal@gmail.com', '09565234632', 2, 2, 'active', '2025-06-14 11:08:00'),
  (10, '2025-00010', 'Patricia', 'Gomez', 'Serrano', 'female', 'patricia.serrano@gmail.com', '09776580278', 2, 2, 'active', '2025-06-21 10:50:00'),
  (11, '2025-00011', 'Kyle', 'Abella', 'Dizon', 'male', 'kyle.dizon@gmail.com', '09187317935', 3, 2, 'active', '2025-06-07 08:09:00'),
  (12, '2025-00012', 'Shaira', NULL, 'Mangubat', 'female', 'shaira.mangubat@gmail.com', '09274157895', 3, 2, 'active', '2025-06-13 11:07:00'),
  (13, '2026-00001', 'Angelo', 'Perez', 'Tolentino', 'male', 'angelo.tolentino@gmail.com', NULL, 1, 1, 'active', '2026-07-10 14:30:00'),
  (14, '2026-00002', 'Nicole', 'Baylon', 'Yap', 'female', 'nicole.yap@gmail.com', '09396525423', 1, 1, 'active', '2026-07-09 10:31:00'),
  (15, '2026-00003', 'Jasper', NULL, 'Ilagan', 'male', 'jasper.ilagan@gmail.com', '09282960769', 1, 1, 'active', '2026-07-18 09:15:00'),
  (16, '2026-00004', 'Alyssa', 'Morales', 'Ong', 'female', 'alyssa.ong@gmail.com', '09182277521', 2, 1, 'active', '2026-07-27 11:52:00'),
  (17, '2026-00005', 'Renz', 'Catapang', 'Sarmiento', 'male', NULL, '09952211387', 3, 1, 'active', '2026-07-20 11:43:00'),
  (18, '2026-00006', 'Mika', NULL, 'Domingo', 'female', 'mika.domingo@gmail.com', '09456246066', 3, 1, 'active', '2026-07-24 09:31:00'),
  (19, '2024-00001', 'Christian', 'Lao', 'Pascual', 'male', 'christian.pascual@gmail.com', '09174333306', 1, 3, 'active', '2024-06-26 08:44:00'),
  (20, '2024-00002', 'Ella', 'Bacus', 'Cañete', 'female', 'ella.canete@gmail.com', '09187320067', 1, 3, 'active', '2024-06-12 15:20:00'),
  (21, '2024-00003', 'Vincent', NULL, 'Ybañez', 'male', 'vincent.ybanez@gmail.com', '09777082626', 1, 3, 'active', '2024-06-02 13:35:00'),
  (22, '2026-00007', 'John Paul', 'Tiu', 'Enriquez', 'male', 'johnpaul.enriquez@gmail.com', '09457840486', 4, 1, 'active', '2026-07-15 08:12:00'),
  (23, '2026-00008', 'Hannah', 'Quijano', 'Alcantara', 'female', 'hannah.alcantara@gmail.com', '09397375064', 4, 1, 'active', '2026-07-06 16:13:00'),
  (24, '2025-00013', 'Bryan', NULL, 'Sison', 'male', 'bryan.sison@gmail.com', '09772648309', 6, 2, 'active', '2025-06-27 08:02:00'),
  (25, '2025-00014', 'Camille', 'Rosales', 'Abrenica', 'female', 'camille.abrenica@gmail.com', '09286482663', 6, 2, 'active', '2025-06-21 10:35:00'),
  (26, '2025-00015', 'Sam', NULL, 'Rivera', 'other', NULL, '09778557134', 7, 2, 'active', '2025-06-17 08:28:00'),
  (27, '2026-00009', 'Princess', 'Labrador', 'Montemayor', 'female', 'princess.montemayor@gmail.com', '09283229841', 8, 1, 'active', '2026-07-16 10:25:00'),
  (28, '2022-00001', 'Daniel', 'Yu', 'Lumbre', 'male', 'daniel.lumbre@gmail.com', NULL, 1, 4, 'graduated', '2022-06-28 10:49:00'),
  (29, '2025-00016', 'Trisha', NULL, 'Galang', 'prefer_not_to_say', 'trisha.galang@gmail.com', '09391840127', 1, 2, 'inactive', '2025-06-25 09:44:00'),
  (30, '2025-00017', 'Kevin', 'Sy', 'Paredes', 'male', 'kevin.paredes@gmail.com', '09772435150', 2, 2, 'dropped', '2025-06-04 14:34:00');
 
-- 165 enrollments (previous term = completed/dropped, current term = enrolled/dropped)
INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `section_id`, `enrollment_datetime`, `status`) VALUES
  (1, 1, 1, '2026-01-06 12:00:00', 'completed'),
  (2, 1, 3, '2026-01-08 15:35:00', 'completed'),
  (3, 1, 4, '2026-01-10 11:02:00', 'completed'),
  (4, 2, 2, '2026-01-06 16:12:00', 'completed'),
  (5, 2, 3, '2026-01-07 13:47:00', 'completed'),
  (6, 2, 4, '2026-01-06 08:42:00', 'completed'),
  (7, 3, 1, '2026-01-06 16:43:00', 'completed'),
  (8, 3, 3, '2026-01-08 10:52:00', 'completed'),
  (9, 3, 4, '2026-01-05 15:46:00', 'completed'),
  (10, 4, 2, '2026-01-10 16:10:00', 'completed'),
  (11, 4, 3, '2026-01-07 12:20:00', 'completed'),
  (12, 4, 4, '2026-01-05 08:54:00', 'completed'),
  (13, 5, 1, '2026-01-07 10:58:00', 'completed'),
  (14, 5, 3, '2026-01-09 15:35:00', 'completed'),
  (15, 5, 4, '2026-01-10 16:54:00', 'completed'),
  (16, 6, 2, '2026-01-05 15:52:00', 'completed'),
  (17, 6, 3, '2026-01-08 10:02:00', 'completed'),
  (18, 6, 4, '2026-01-06 12:49:00', 'completed'),
  (19, 7, 1, '2026-01-07 09:04:00', 'completed'),
  (20, 7, 3, '2026-01-06 12:02:00', 'completed'),
  (21, 7, 4, '2026-01-10 16:28:00', 'dropped'),
  (22, 8, 2, '2026-01-08 13:15:00', 'completed'),
  (23, 8, 3, '2026-01-10 15:04:00', 'completed'),
  (24, 8, 4, '2026-01-08 10:04:00', 'completed'),
  (25, 9, 1, '2026-01-08 09:43:00', 'completed'),
  (26, 9, 3, '2026-01-08 16:09:00', 'completed'),
  (27, 9, 4, '2026-01-10 09:43:00', 'completed'),
  (28, 10, 2, '2026-01-06 08:44:00', 'completed'),
  (29, 10, 3, '2026-01-07 13:21:00', 'completed'),
  (30, 10, 4, '2026-01-06 11:41:00', 'completed'),
  (31, 11, 1, '2026-01-07 16:02:00', 'completed'),
  (32, 11, 3, '2026-01-06 08:37:00', 'completed'),
  (33, 11, 4, '2026-01-07 08:41:00', 'completed'),
  (34, 12, 2, '2026-01-06 12:48:00', 'completed'),
  (35, 12, 3, '2026-01-06 15:59:00', 'completed'),
  (36, 12, 4, '2026-01-09 08:42:00', 'completed'),
  (37, 29, 1, '2026-01-09 15:31:00', 'completed'),
  (38, 29, 3, '2026-01-10 11:55:00', 'completed'),
  (39, 29, 4, '2026-01-05 14:45:00', 'completed'),
  (40, 30, 2, '2026-01-09 14:20:00', 'dropped'),
  (41, 30, 3, '2026-01-06 11:32:00', 'dropped'),
  (42, 30, 4, '2026-01-06 11:03:00', 'dropped'),
  (43, 19, 5, '2026-01-08 16:03:00', 'completed'),
  (44, 20, 5, '2026-01-05 14:09:00', 'completed'),
  (45, 21, 5, '2026-01-09 11:58:00', 'completed'),
  (46, 24, 3, '2026-01-10 08:43:00', 'completed'),
  (47, 24, 4, '2026-01-10 13:01:00', 'completed'),
  (48, 25, 3, '2026-01-06 10:59:00', 'completed'),
  (49, 25, 4, '2026-01-10 09:36:00', 'completed'),
  (50, 26, 3, '2026-01-09 15:40:00', 'completed'),
  (51, 26, 4, '2026-01-09 10:19:00', 'completed'),
  (52, 1, 6, '2026-07-27 12:00:00', 'enrolled'),
  (53, 1, 7, '2026-07-27 14:47:00', 'enrolled'),
  (54, 1, 9, '2026-07-30 12:56:00', 'enrolled'),
  (55, 1, 10, '2026-07-30 11:47:00', 'enrolled'),
  (56, 1, 11, '2026-07-27 14:37:00', 'enrolled'),
  (57, 1, 12, '2026-07-30 12:43:00', 'enrolled'),
  (58, 2, 6, '2026-07-28 12:18:00', 'enrolled'),
  (59, 2, 7, '2026-07-30 12:48:00', 'enrolled'),
  (60, 2, 9, '2026-07-30 08:41:00', 'enrolled'),
  (61, 2, 10, '2026-07-30 09:37:00', 'enrolled'),
  (62, 2, 11, '2026-07-27 14:54:00', 'enrolled'),
  (63, 2, 12, '2026-07-31 13:07:00', 'enrolled'),
  (64, 3, 6, '2026-07-29 16:12:00', 'enrolled'),
  (65, 3, 7, '2026-07-30 11:15:00', 'enrolled'),
  (66, 3, 9, '2026-07-31 09:24:00', 'enrolled'),
  (67, 3, 10, '2026-07-29 15:04:00', 'enrolled'),
  (68, 3, 11, '2026-07-28 12:34:00', 'enrolled'),
  (69, 3, 12, '2026-07-31 09:19:00', 'enrolled'),
  (70, 4, 6, '2026-07-29 11:44:00', 'enrolled'),
  (71, 4, 7, '2026-07-28 15:36:00', 'enrolled'),
  (72, 4, 9, '2026-07-30 10:57:00', 'enrolled'),
  (73, 4, 10, '2026-07-28 09:47:00', 'enrolled'),
  (74, 4, 11, '2026-07-29 11:54:00', 'enrolled'),
  (75, 4, 12, '2026-07-28 16:29:00', 'enrolled'),
  (76, 5, 6, '2026-07-30 12:31:00', 'enrolled'),
  (77, 5, 7, '2026-07-29 13:39:00', 'enrolled'),
  (78, 5, 9, '2026-07-27 16:22:00', 'dropped'),
  (79, 5, 10, '2026-07-27 10:37:00', 'enrolled'),
  (80, 5, 11, '2026-07-28 15:27:00', 'enrolled'),
  (81, 5, 12, '2026-07-28 14:58:00', 'enrolled'),
  (82, 6, 6, '2026-07-28 09:47:00', 'enrolled'),
  (83, 6, 7, '2026-07-30 09:58:00', 'enrolled'),
  (84, 6, 9, '2026-07-31 16:07:00', 'enrolled'),
  (85, 6, 10, '2026-07-30 11:14:00', 'enrolled'),
  (86, 6, 11, '2026-07-31 10:55:00', 'enrolled'),
  (87, 6, 12, '2026-07-29 15:01:00', 'enrolled'),
  (88, 7, 6, '2026-07-29 12:31:00', 'enrolled'),
  (89, 7, 7, '2026-07-31 15:50:00', 'enrolled'),
  (90, 7, 9, '2026-07-30 13:08:00', 'enrolled'),
  (91, 7, 10, '2026-07-27 11:46:00', 'enrolled'),
  (92, 7, 11, '2026-07-27 13:11:00', 'enrolled'),
  (93, 7, 12, '2026-07-28 10:45:00', 'enrolled'),
  (94, 8, 8, '2026-07-27 08:18:00', 'enrolled'),
  (95, 8, 7, '2026-07-31 15:00:00', 'enrolled'),
  (96, 8, 9, '2026-07-27 09:45:00', 'enrolled'),
  (97, 8, 10, '2026-07-28 13:48:00', 'enrolled'),
  (98, 8, 11, '2026-07-27 09:18:00', 'enrolled'),
  (99, 8, 12, '2026-07-31 13:42:00', 'enrolled'),
  (100, 9, 8, '2026-07-29 14:57:00', 'enrolled'),
  (101, 9, 7, '2026-07-29 15:22:00', 'enrolled'),
  (102, 9, 9, '2026-07-28 09:47:00', 'enrolled'),
  (103, 9, 10, '2026-07-27 11:21:00', 'enrolled'),
  (104, 9, 11, '2026-07-28 14:37:00', 'enrolled'),
  (105, 9, 12, '2026-07-31 11:16:00', 'enrolled'),
  (106, 10, 8, '2026-07-31 14:37:00', 'enrolled'),
  (107, 10, 7, '2026-07-30 13:58:00', 'enrolled'),
  (108, 10, 9, '2026-07-28 10:52:00', 'enrolled'),
  (109, 10, 10, '2026-07-28 11:21:00', 'enrolled'),
  (110, 10, 11, '2026-07-30 09:10:00', 'enrolled'),
  (111, 10, 12, '2026-07-31 12:31:00', 'enrolled'),
  (112, 11, 8, '2026-07-30 10:24:00', 'enrolled'),
  (113, 11, 7, '2026-07-31 13:19:00', 'enrolled'),
  (114, 11, 9, '2026-07-27 10:30:00', 'enrolled'),
  (115, 11, 10, '2026-07-28 13:33:00', 'enrolled'),
  (116, 11, 11, '2026-07-31 15:29:00', 'enrolled'),
  (117, 11, 12, '2026-07-31 10:44:00', 'enrolled'),
  (118, 12, 8, '2026-07-30 11:46:00', 'enrolled'),
  (119, 12, 7, '2026-07-27 12:43:00', 'enrolled'),
  (120, 12, 9, '2026-07-27 15:57:00', 'enrolled'),
  (121, 12, 10, '2026-07-29 10:18:00', 'enrolled'),
  (122, 12, 11, '2026-07-30 14:31:00', 'enrolled'),
  (123, 12, 12, '2026-07-29 14:38:00', 'enrolled'),
  (124, 13, 13, '2026-07-28 15:32:00', 'enrolled'),
  (125, 13, 14, '2026-07-29 08:44:00', 'enrolled'),
  (126, 13, 15, '2026-07-27 08:28:00', 'enrolled'),
  (127, 13, 16, '2026-07-27 14:08:00', 'enrolled'),
  (128, 14, 13, '2026-07-29 11:44:00', 'enrolled'),
  (129, 14, 14, '2026-07-29 15:12:00', 'enrolled'),
  (130, 14, 15, '2026-07-27 13:33:00', 'enrolled'),
  (131, 14, 16, '2026-07-28 14:47:00', 'enrolled'),
  (132, 15, 13, '2026-07-31 09:28:00', 'enrolled'),
  (133, 15, 14, '2026-07-30 11:03:00', 'enrolled'),
  (134, 15, 15, '2026-07-27 14:29:00', 'enrolled'),
  (135, 15, 16, '2026-07-29 09:14:00', 'enrolled'),
  (136, 16, 13, '2026-07-31 14:30:00', 'enrolled'),
  (137, 16, 14, '2026-07-31 11:05:00', 'enrolled'),
  (138, 16, 15, '2026-07-27 15:39:00', 'enrolled'),
  (139, 16, 16, '2026-07-27 10:45:00', 'enrolled'),
  (140, 17, 13, '2026-07-27 09:18:00', 'enrolled'),
  (141, 17, 14, '2026-07-27 11:29:00', 'enrolled'),
  (142, 17, 15, '2026-07-31 08:07:00', 'enrolled'),
  (143, 17, 16, '2026-07-29 15:02:00', 'enrolled'),
  (144, 18, 13, '2026-07-28 13:46:00', 'enrolled'),
  (145, 18, 14, '2026-07-28 11:02:00', 'enrolled'),
  (146, 18, 15, '2026-07-27 14:02:00', 'enrolled'),
  (147, 18, 16, '2026-07-27 09:27:00', 'enrolled'),
  (148, 19, 17, '2026-07-31 08:34:00', 'enrolled'),
  (149, 19, 11, '2026-07-27 12:20:00', 'enrolled'),
  (150, 20, 17, '2026-07-27 15:52:00', 'enrolled'),
  (151, 20, 11, '2026-07-31 08:36:00', 'enrolled'),
  (152, 21, 17, '2026-07-29 14:10:00', 'enrolled'),
  (153, 21, 11, '2026-07-27 14:58:00', 'enrolled'),
  (154, 22, 18, '2026-07-28 11:35:00', 'enrolled'),
  (155, 22, 15, '2026-07-30 12:20:00', 'enrolled'),
  (156, 23, 18, '2026-07-31 08:03:00', 'enrolled'),
  (157, 23, 15, '2026-07-27 10:30:00', 'enrolled'),
  (158, 24, 19, '2026-07-29 12:17:00', 'enrolled'),
  (159, 24, 11, '2026-07-30 13:50:00', 'enrolled'),
  (160, 25, 19, '2026-07-30 09:38:00', 'enrolled'),
  (161, 25, 11, '2026-07-30 13:50:00', 'enrolled'),
  (162, 26, 19, '2026-07-31 13:03:00', 'enrolled'),
  (163, 26, 11, '2026-07-27 08:14:00', 'enrolled'),
  (164, 27, 20, '2026-07-29 16:38:00', 'enrolled'),
  (165, 27, 16, '2026-07-27 10:56:00', 'enrolled');
 
-- 84 payments (students 15, 23, 27 have not paid this term yet)
INSERT INTO `payments` (`payment_id`, `student_id`, `school_year`, `semester`, `amount`, `payment_method_id`, `payment_datetime`) VALUES
  (1, 1, '2025-2026', '2nd', '5000.00', 1, '2026-01-09 15:30:00'),
  (2, 1, '2025-2026', '2nd', '8000.00', 1, '2026-03-16 16:05:00'),
  (3, 2, '2025-2026', '2nd', '5000.00', 6, '2026-01-09 12:56:00'),
  (4, 2, '2025-2026', '2nd', '6000.00', 1, '2026-03-15 10:45:00'),
  (5, 3, '2025-2026', '2nd', '5000.00', 2, '2026-01-06 10:17:00'),
  (6, 3, '2025-2026', '2nd', '7500.00', 1, '2026-03-12 15:31:00'),
  (7, 4, '2025-2026', '2nd', '5000.00', 2, '2026-01-06 13:50:00'),
  (8, 4, '2025-2026', '2nd', '8000.00', 3, '2026-03-06 13:53:00'),
  (9, 5, '2025-2026', '2nd', '5000.00', 6, '2026-01-10 14:44:00'),
  (10, 5, '2025-2026', '2nd', '7500.00', 1, '2026-03-03 12:02:00'),
  (11, 6, '2025-2026', '2nd', '5000.00', 6, '2026-01-10 16:51:00'),
  (12, 6, '2025-2026', '2nd', '7500.00', 4, '2026-03-16 15:15:00'),
  (13, 7, '2025-2026', '2nd', '5000.00', 2, '2026-01-05 16:20:00'),
  (14, 7, '2025-2026', '2nd', '6000.00', 1, '2026-03-10 13:42:00'),
  (15, 8, '2025-2026', '2nd', '5000.00', 2, '2026-01-08 15:13:00'),
  (16, 8, '2025-2026', '2nd', '8000.00', 2, '2026-03-06 11:43:00'),
  (17, 9, '2025-2026', '2nd', '5000.00', 2, '2026-01-05 13:41:00'),
  (18, 9, '2025-2026', '2nd', '8000.00', 1, '2026-03-19 09:32:00'),
  (19, 10, '2025-2026', '2nd', '5000.00', 2, '2026-01-05 16:35:00'),
  (20, 10, '2025-2026', '2nd', '8000.00', 2, '2026-03-11 14:21:00'),
  (21, 11, '2025-2026', '2nd', '5000.00', 1, '2026-01-10 11:07:00'),
  (22, 11, '2025-2026', '2nd', '6000.00', 3, '2026-03-11 16:26:00'),
  (23, 12, '2025-2026', '2nd', '5000.00', 6, '2026-01-09 11:02:00'),
  (24, 12, '2025-2026', '2nd', '7500.00', 1, '2026-03-15 08:12:00'),
  (25, 19, '2025-2026', '2nd', '5000.00', 1, '2026-01-10 08:13:00'),
  (26, 19, '2025-2026', '2nd', '7500.00', 1, '2026-03-16 11:30:00'),
  (27, 20, '2025-2026', '2nd', '5000.00', 2, '2026-01-08 16:54:00'),
  (28, 20, '2025-2026', '2nd', '6000.00', 2, '2026-03-12 12:11:00'),
  (29, 21, '2025-2026', '2nd', '5000.00', 1, '2026-01-09 09:18:00'),
  (30, 21, '2025-2026', '2nd', '8000.00', 1, '2026-03-08 13:33:00'),
  (31, 24, '2025-2026', '2nd', '5000.00', 1, '2026-01-09 11:24:00'),
  (32, 24, '2025-2026', '2nd', '7500.00', 4, '2026-03-03 08:54:00'),
  (33, 25, '2025-2026', '2nd', '5000.00', 6, '2026-01-06 10:16:00'),
  (34, 25, '2025-2026', '2nd', '8000.00', 1, '2026-03-16 16:02:00'),
  (35, 26, '2025-2026', '2nd', '5000.00', 2, '2026-01-06 13:25:00'),
  (36, 26, '2025-2026', '2nd', '6000.00', 3, '2026-03-09 15:47:00'),
  (37, 29, '2025-2026', '2nd', '5000.00', 1, '2026-01-10 11:37:00'),
  (38, 29, '2025-2026', '2nd', '7500.00', 2, '2026-03-14 12:03:00'),
  (39, 30, '2025-2026', '2nd', '3000.00', 1, '2026-01-08 10:15:00'),
  (40, 1, '2026-2027', '1st', '5000.00', 3, '2026-07-27 15:04:00'),
  (41, 1, '2026-2027', '1st', '3500.00', 3, '2026-10-17 15:37:00'),
  (42, 2, '2026-2027', '1st', '5000.00', 5, '2026-07-29 15:24:00'),
  (43, 2, '2026-2027', '1st', '6500.00', 1, '2026-09-21 13:36:00'),
  (44, 3, '2026-2027', '1st', '3000.00', 5, '2026-07-31 15:09:00'),
  (45, 3, '2026-2027', '1st', '4000.00', 4, '2026-09-10 10:25:00'),
  (46, 3, '2026-2027', '1st', '3500.00', 2, '2026-10-28 15:24:00'),
  (47, 4, '2026-2027', '1st', '3000.00', 1, '2026-07-28 16:16:00'),
  (48, 4, '2026-2027', '1st', '4000.00', 2, '2026-09-11 13:51:00'),
  (49, 5, '2026-2027', '1st', '3000.00', 3, '2026-07-30 16:16:00'),
  (50, 6, '2026-2027', '1st', '3000.00', 2, '2026-07-31 08:17:00'),
  (51, 6, '2026-2027', '1st', '5000.00', 2, '2026-10-26 11:12:00'),
  (52, 7, '2026-2027', '1st', '5000.00', 1, '2026-07-29 15:31:00'),
  (53, 7, '2026-2027', '1st', '5000.00', 1, '2026-09-18 16:06:00'),
  (54, 8, '2026-2027', '1st', '5000.00', 2, '2026-07-30 13:14:00'),
  (55, 8, '2026-2027', '1st', '4000.00', 2, '2026-09-17 10:24:00'),
  (56, 9, '2026-2027', '1st', '3000.00', 2, '2026-07-28 15:23:00'),
  (57, 9, '2026-2027', '1st', '6500.00', 3, '2026-09-16 08:01:00'),
  (58, 10, '2026-2027', '1st', '3000.00', 3, '2026-07-29 08:49:00'),
  (59, 10, '2026-2027', '1st', '4000.00', 4, '2026-09-12 10:52:00'),
  (60, 11, '2026-2027', '1st', '3000.00', 3, '2026-07-27 14:03:00'),
  (61, 11, '2026-2027', '1st', '5000.00', 4, '2026-10-24 11:56:00'),
  (62, 12, '2026-2027', '1st', '3000.00', 5, '2026-07-27 15:07:00'),
  (63, 12, '2026-2027', '1st', '5000.00', 4, '2026-09-17 10:31:00'),
  (64, 13, '2026-2027', '1st', '3000.00', 3, '2026-07-30 09:53:00'),
  (65, 13, '2026-2027', '1st', '4000.00', 3, '2026-09-16 13:42:00'),
  (66, 13, '2026-2027', '1st', '5000.00', 2, '2026-10-16 13:30:00'),
  (67, 14, '2026-2027', '1st', '3000.00', 3, '2026-07-31 09:12:00'),
  (68, 14, '2026-2027', '1st', '5000.00', 3, '2026-09-23 11:24:00'),
  (69, 14, '2026-2027', '1st', '5000.00', 2, '2026-10-17 14:28:00'),
  (70, 16, '2026-2027', '1st', '3000.00', 2, '2026-07-29 09:04:00'),
  (71, 17, '2026-2027', '1st', '5000.00', 3, '2026-07-31 16:52:00'),
  (72, 17, '2026-2027', '1st', '5000.00', 2, '2026-09-10 09:52:00'),
  (73, 18, '2026-2027', '1st', '5000.00', 2, '2026-07-30 16:57:00'),
  (74, 19, '2026-2027', '1st', '3000.00', 5, '2026-07-28 08:00:00'),
  (75, 20, '2026-2027', '1st', '5000.00', 1, '2026-07-31 15:30:00'),
  (76, 20, '2026-2027', '1st', '4000.00', 2, '2026-09-19 09:45:00'),
  (77, 20, '2026-2027', '1st', '3500.00', 2, '2026-10-17 15:15:00'),
  (78, 21, '2026-2027', '1st', '3000.00', 3, '2026-07-31 16:29:00'),
  (79, 22, '2026-2027', '1st', '3000.00', 3, '2026-07-27 13:44:00'),
  (80, 22, '2026-2027', '1st', '6500.00', 1, '2026-09-21 08:32:00'),
  (81, 24, '2026-2027', '1st', '5000.00', 5, '2026-07-30 10:02:00'),
  (82, 25, '2026-2027', '1st', '3000.00', 1, '2026-07-28 11:18:00'),
  (83, 25, '2026-2027', '1st', '6500.00', 4, '2026-09-20 11:45:00'),
  (84, 26, '2026-2027', '1st', '3000.00', 5, '2026-07-27 08:25:00');
 
COMMIT;


