<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        foreach ([
            'v_user_accounts',
            'v_student_term_summary',
            'v_payment_history',
            'v_enrollment_details',
            'v_section_availability',
            'v_student_details',
        ] as $view) {
            DB::statement("DROP VIEW IF EXISTS {$view}");
        }

        $fullName = fn (string $alias): string => $driver === 'sqlite'
            ? "TRIM({$alias}.first_name || ' ' || COALESCE({$alias}.middle_name || ' ', '') || {$alias}.last_name)"
            : "CONCAT_WS(' ', {$alias}.first_name, {$alias}.middle_name, {$alias}.last_name)";
        $instructorName = $driver === 'sqlite'
            ? "TRIM(COALESCE(i.first_name || ' ', '') || COALESCE(i.last_name, ''))"
            : "CONCAT_WS(' ', i.first_name, i.last_name)";

        DB::statement("CREATE VIEW v_student_details AS
            SELECT s.student_id, s.student_no, {$fullName('s')} AS full_name, s.gender, s.email,
                   s.contact_no, s.year_level, s.status, p.program_code, p.program_name,
                   d.department_code
            FROM students s
            LEFT JOIN programs p ON p.program_id = s.program_id
            LEFT JOIN departments d ON d.department_id = p.department_id");

        DB::statement("CREATE VIEW v_section_availability AS
            SELECT sec.section_id, sec.section_code, sub.subject_code, sub.subject_name, sub.units,
                   {$instructorName} AS instructor, sec.school_year, sec.semester,
                   sec.schedule_day, sec.start_time, sec.end_time, sec.room, sec.max_capacity,
                   (SELECT COUNT(*) FROM enrollments e
                    WHERE e.section_id = sec.section_id AND e.status = 'Enrolled') AS enrolled_count,
                   sec.max_capacity - (SELECT COUNT(*) FROM enrollments e
                    WHERE e.section_id = sec.section_id AND e.status = 'Enrolled') AS available_slots
            FROM sections sec
            JOIN subjects sub ON sub.subject_id = sec.subject_id
            LEFT JOIN instructors i ON i.instructor_id = sec.instructor_id");

        DB::statement("CREATE VIEW v_enrollment_details AS
            SELECT e.enrollment_id, s.student_no, {$fullName('s')} AS student_name,
                   sec.section_code, sub.subject_code, sub.subject_name, sec.school_year,
                   sec.semester, e.enrollment_datetime, e.status, u.username AS processed_by
            FROM enrollments e
            JOIN students s ON s.student_id = e.student_id
            JOIN sections sec ON sec.section_id = e.section_id
            JOIN subjects sub ON sub.subject_id = sec.subject_id
            LEFT JOIN users u ON u.id = e.processed_by");

        DB::statement("CREATE VIEW v_payment_history AS
            SELECT p.payment_id, st.student_no, {$fullName('st')} AS student_name, p.school_year,
                   p.semester, p.amount, pm.method_name, p.payment_datetime,
                   u.username AS recorded_by
            FROM payments p
            JOIN students st ON st.student_id = p.student_id
            JOIN payment_methods pm ON pm.payment_method_id = p.payment_method_id
            LEFT JOIN users u ON u.id = p.recorded_by");

        DB::statement("CREATE VIEW v_student_term_summary AS
            SELECT st.student_id, st.student_no, {$fullName('st')} AS student_name,
                   sec.school_year, sec.semester, COUNT(*) AS subjects_enrolled,
                   SUM(sub.units) AS total_units,
                   COALESCE((SELECT SUM(pay.amount) FROM payments pay
                     WHERE pay.student_id = st.student_id AND pay.school_year = sec.school_year
                       AND pay.semester = sec.semester), 0) AS total_paid
            FROM enrollments e
            JOIN students st ON st.student_id = e.student_id
            JOIN sections sec ON sec.section_id = e.section_id
            JOIN subjects sub ON sub.subject_id = sec.subject_id
            WHERE e.status = 'Enrolled'
            GROUP BY st.student_id, st.student_no, st.first_name, st.middle_name, st.last_name,
                     sec.school_year, sec.semester");

        DB::statement('CREATE VIEW v_user_accounts AS
            SELECT id AS user_id, username, email, first_name, last_name, role, status,
                   last_login, created_at FROM users');
    }

    public function down(): void
    {
        foreach ([
            'v_user_accounts',
            'v_student_term_summary',
            'v_payment_history',
            'v_enrollment_details',
            'v_section_availability',
            'v_student_details',
        ] as $view) {
            DB::statement("DROP VIEW IF EXISTS {$view}");
        }
    }
};
