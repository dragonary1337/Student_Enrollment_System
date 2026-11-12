<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
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

            foreach (['sp_record_payment', 'sp_drop_add', 'sp_enroll_student'] as $procedure) {
                DB::unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
            }

            foreach (['fn_total_paid', 'fn_total_units', 'fn_available_slots'] as $function) {
                DB::unprepared("DROP FUNCTION IF EXISTS {$function}");
            }
        }

        foreach ([
            'students' => ['idx_students_name', 'idx_students_status'],
            'sections' => ['idx_sections_term'],
            'users' => ['idx_users_role_status'],
            'enrollments' => ['idx_enrollments_section_status'],
            'payments' => ['idx_payments_student_term'],
        ] as $tableName => $indexes) {
            foreach ($indexes as $indexName) {
                if ($this->indexExists($driver, $tableName, $indexName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
                        $table->dropIndex($indexName);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        // The removed database features are intentionally not restored.
    }

    private function indexExists(string $driver, string $tableName, string $indexName): bool
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return DB::selectOne("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]) !== null;
        }

        if ($driver === 'sqlite') {
            return DB::selectOne(
                'SELECT 1 FROM sqlite_master WHERE type = ? AND tbl_name = ? AND name = ?',
                ['index', $tableName, $indexName],
            ) !== null;
        }

        return false;
    }
};
