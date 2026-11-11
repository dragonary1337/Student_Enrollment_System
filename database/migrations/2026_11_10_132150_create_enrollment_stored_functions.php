<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::unprepared("CREATE FUNCTION fn_available_slots(p_section_id BIGINT)
            RETURNS INT READS SQL DATA
            RETURN (SELECT s.max_capacity -
                (SELECT COUNT(*) FROM enrollments e
                 WHERE e.section_id = s.section_id AND e.status = 'Enrolled')
                FROM sections s WHERE s.section_id = p_section_id)");

        DB::unprepared("CREATE FUNCTION fn_total_units(
            p_student_id BIGINT, p_school_year VARCHAR(9), p_semester VARCHAR(6))
            RETURNS DECIMAL(5,1) READS SQL DATA
            RETURN COALESCE((SELECT SUM(sub.units)
                FROM enrollments e
                JOIN sections sec ON sec.section_id = e.section_id
                JOIN subjects sub ON sub.subject_id = sec.subject_id
                WHERE e.student_id = p_student_id AND e.status = 'Enrolled'
                  AND sec.school_year = p_school_year AND sec.semester = p_semester), 0)");

        DB::unprepared('CREATE FUNCTION fn_total_paid(
            p_student_id BIGINT, p_school_year VARCHAR(9), p_semester VARCHAR(6))
            RETURNS DECIMAL(10,2) READS SQL DATA
            RETURN COALESCE((SELECT SUM(amount) FROM payments
                WHERE student_id = p_student_id AND school_year = p_school_year
                  AND semester = p_semester), 0)');

        DB::unprepared("CREATE PROCEDURE sp_enroll_student(
            IN p_student_id BIGINT, IN p_section_id BIGINT, IN p_user_id BIGINT)
            BEGIN
                DECLARE v_status VARCHAR(20);
                DECLARE v_existing BIGINT DEFAULT NULL;
                DECLARE v_existing_status VARCHAR(20) DEFAULT NULL;
                DECLARE v_existing_count INT DEFAULT 0;
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;
                START TRANSACTION;
                SELECT status INTO v_status FROM users WHERE id = p_user_id;
                IF v_status IS NULL OR v_status <> 'Active' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only Active users can record enrollments.';
                END IF;
                SELECT section_id INTO @ses_locked_section FROM sections
                WHERE section_id = p_section_id FOR UPDATE;
                SELECT COUNT(*) INTO v_existing_count
                FROM enrollments WHERE student_id = p_student_id AND section_id = p_section_id;
                IF v_existing_count > 0 THEN
                    SELECT enrollment_id, status INTO v_existing, v_existing_status
                    FROM enrollments WHERE student_id = p_student_id AND section_id = p_section_id
                    FOR UPDATE;
                END IF;
                IF v_existing IS NULL THEN
                    INSERT INTO enrollments (student_id, section_id, processed_by)
                    VALUES (p_student_id, p_section_id, p_user_id);
                ELSEIF v_existing_status = 'Enrolled' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The student is already enrolled.';
                ELSE
                    UPDATE enrollments SET status = 'Enrolled', enrollment_datetime = NOW(),
                    processed_by = p_user_id WHERE enrollment_id = v_existing;
                END IF;
                COMMIT;
            END");

        DB::unprepared("CREATE PROCEDURE sp_drop_add(
            IN p_enrollment_id BIGINT, IN p_new_section_id BIGINT, IN p_user_id BIGINT,
            OUT p_new_enrollment_id BIGINT)
            BEGIN
                DECLARE v_user_status VARCHAR(20);
                DECLARE v_student_status VARCHAR(20);
                DECLARE v_student_id BIGINT;
                DECLARE v_old_section_id BIGINT;
                DECLARE v_old_year VARCHAR(9);
                DECLARE v_old_semester VARCHAR(6);
                DECLARE v_new_year VARCHAR(9);
                DECLARE v_new_semester VARCHAR(6);
                DECLARE v_old_status VARCHAR(20);
                DECLARE v_existing_id BIGINT DEFAULT NULL;
                DECLARE v_existing_status VARCHAR(20) DEFAULT NULL;
                DECLARE v_row_count INT DEFAULT 0;
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;
                START TRANSACTION;
                SELECT status INTO v_user_status FROM users WHERE id = p_user_id FOR UPDATE;
                IF v_user_status IS NULL OR v_user_status <> 'Active' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only Active users can process drop/add.';
                END IF;
                SELECT COUNT(*) INTO v_row_count FROM enrollments WHERE enrollment_id = p_enrollment_id;
                IF v_row_count = 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The enrollment does not exist.';
                END IF;
                SELECT student_id, section_id, status
                INTO v_student_id, v_old_section_id, v_old_status
                FROM enrollments WHERE enrollment_id = p_enrollment_id FOR UPDATE;
                IF v_old_status <> 'Enrolled' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The enrollment is not active.';
                END IF;
                SELECT status INTO v_student_status FROM students WHERE student_id = v_student_id;
                IF v_student_status <> 'Active' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only Active students may be moved to another section.';
                END IF;
                SELECT school_year, semester INTO v_old_year, v_old_semester
                FROM sections WHERE section_id = v_old_section_id;
                SELECT COUNT(*) INTO v_row_count FROM sections WHERE section_id = p_new_section_id;
                IF v_row_count = 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The replacement section does not exist.';
                END IF;
                SELECT school_year, semester INTO v_new_year, v_new_semester
                FROM sections WHERE section_id = p_new_section_id FOR UPDATE;
                IF v_new_year <> v_old_year OR v_new_semester <> v_old_semester THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Drop/add must use a section in the same term.';
                END IF;
                SELECT MAX(enrollment_id) INTO v_existing_id
                FROM enrollments
                WHERE student_id = v_student_id AND section_id = p_new_section_id;
                IF v_existing_id IS NOT NULL THEN
                    SELECT status INTO v_existing_status FROM enrollments
                    WHERE enrollment_id = v_existing_id FOR UPDATE;
                END IF;
                IF v_existing_status = 'Enrolled' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The student is already enrolled in the selected section.';
                END IF;
                UPDATE enrollments SET status = 'Dropped', processed_by = p_user_id
                WHERE enrollment_id = p_enrollment_id;
                IF v_existing_id IS NULL THEN
                    INSERT INTO enrollments (student_id, section_id, processed_by)
                    VALUES (v_student_id, p_new_section_id, p_user_id);
                    SET p_new_enrollment_id = LAST_INSERT_ID();
                ELSE
                    UPDATE enrollments SET status = 'Enrolled', enrollment_datetime = NOW(),
                    processed_by = p_user_id WHERE enrollment_id = v_existing_id;
                    SET p_new_enrollment_id = v_existing_id;
                END IF;
                COMMIT;
            END");

        DB::unprepared("CREATE PROCEDURE sp_record_payment(
            IN p_student_id BIGINT, IN p_school_year VARCHAR(9), IN p_semester VARCHAR(6),
            IN p_amount DECIMAL(10,2), IN p_payment_method_id BIGINT, IN p_user_id BIGINT,
            OUT p_payment_id BIGINT)
            BEGIN
                DECLARE v_status VARCHAR(20);
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;
                START TRANSACTION;
                SELECT status INTO v_status FROM users WHERE id = p_user_id;
                IF v_status IS NULL OR v_status <> 'Active' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only Active users can record payments.';
                END IF;
                INSERT INTO payments (student_id, school_year, semester, amount, payment_method_id, recorded_by)
                VALUES (p_student_id, p_school_year, p_semester, p_amount, p_payment_method_id, p_user_id);
                SET p_payment_id = LAST_INSERT_ID();
                COMMIT;
            END");
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_record_payment');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_drop_add');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_enroll_student');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_total_paid');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_total_units');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_available_slots');
    }
};
