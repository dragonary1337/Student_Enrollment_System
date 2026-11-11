<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->string('student_no', 10)->unique();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('contact_no', 20)->nullable();
            $table->foreignId('program_id')->nullable()->constrained('programs', 'program_id')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedTinyInteger('year_level');
            $table->enum('status', ['Active', 'Inactive', 'Graduated', 'Dropped'])->default('Active');
            $table->timestamp('date_registered')->useCurrent();
            $table->index(['last_name', 'first_name'], 'idx_students_name');
            $table->index('status', 'idx_students_status');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE students ADD CONSTRAINT chk_students_year CHECK (year_level BETWEEN 1 AND 5)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
