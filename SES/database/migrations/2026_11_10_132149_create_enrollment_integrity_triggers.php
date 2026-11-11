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

        DB::unprepared("CREATE TRIGGER trg_enrollments_before_insert
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
            END");

        DB::unprepared("CREATE TRIGGER trg_sections_before_update
            BEFORE UPDATE ON sections FOR EACH ROW
            BEGIN
                DECLARE v_enrolled INT;
                SELECT COUNT(*) INTO v_enrolled FROM enrollments
                WHERE section_id = OLD.section_id AND status = 'Enrolled';
                IF NEW.max_capacity < v_enrolled THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Section capacity cannot be reduced below its current enrollment.';
                END IF;
            END");

        DB::unprepared("CREATE TRIGGER trg_enrollments_before_update
            BEFORE UPDATE ON enrollments FOR EACH ROW
            BEGIN
                DECLARE v_capacity INT;
                DECLARE v_enrolled INT;
                DECLARE v_student_status VARCHAR(20);
                IF NEW.status = 'Enrolled' THEN
                    SELECT status INTO v_student_status FROM students WHERE student_id = NEW.student_id;
                    IF v_student_status <> 'Active' THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only Active students can be enrolled.';
                    END IF;
                END IF;
                IF NEW.status = 'Enrolled'
                    AND (OLD.status <> 'Enrolled' OR OLD.section_id <> NEW.section_id) THEN
                    SELECT max_capacity INTO v_capacity FROM sections WHERE section_id = NEW.section_id;
                    SELECT COUNT(*) INTO v_enrolled FROM enrollments
                    WHERE section_id = NEW.section_id AND status = 'Enrolled';
                    IF v_enrolled >= v_capacity THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The section is already full.';
                    END IF;
                END IF;
            END");

        DB::unprepared("CREATE TRIGGER trg_payments_before_insert
            BEFORE INSERT ON payments FOR EACH ROW
            BEGIN
                DECLARE v_available BOOLEAN;
                DECLARE v_student_status VARCHAR(20);
                SELECT status INTO v_student_status FROM students WHERE student_id = NEW.student_id;
                IF v_student_status <> 'Active' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payments require an Active student.';
                END IF;
                SELECT method_available INTO v_available FROM payment_methods
                WHERE payment_method_id = NEW.payment_method_id;
                IF v_available = FALSE THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The selected payment method is not available.';
                END IF;
                IF NOT EXISTS (
                    SELECT 1 FROM enrollments e
                    JOIN sections s ON s.section_id = e.section_id
                    WHERE e.student_id = NEW.student_id AND e.status = 'Enrolled'
                    AND s.school_year = NEW.school_year AND s.semester = NEW.semester
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The student has no enrollment for this term.';
                END IF;
            END");

        DB::unprepared("CREATE TRIGGER trg_users_before_update
            BEFORE UPDATE ON users FOR EACH ROW
            BEGIN
                IF OLD.role = 'Admin' AND OLD.status = 'Active'
                    AND (NEW.role <> 'Admin' OR NEW.status <> 'Active')
                    AND (SELECT COUNT(*) FROM users WHERE role = 'Admin' AND status = 'Active') <= 1 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The last Active Admin cannot be deactivated or demoted.';
                END IF;
            END");

        DB::unprepared("CREATE TRIGGER trg_users_before_delete
            BEFORE DELETE ON users FOR EACH ROW
            BEGIN
                IF OLD.role = 'Admin' AND OLD.status = 'Active'
                    AND (SELECT COUNT(*) FROM users WHERE role = 'Admin' AND status = 'Active') <= 1 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The last Active Admin cannot be deleted.';
                END IF;
            END");
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach ([
            'trg_users_before_delete',
            'trg_users_before_update',
            'trg_payments_before_insert',
            'trg_enrollments_before_update',
            'trg_sections_before_update',
            'trg_enrollments_before_insert',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
