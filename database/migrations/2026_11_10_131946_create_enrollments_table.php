<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id('enrollment_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('section_id')->constrained('sections', 'section_id')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamp('enrollment_datetime')->useCurrent();
            $table->enum('status', ['Enrolled', 'Dropped', 'Completed'])->default('Enrolled');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->unique(['student_id', 'section_id'], 'uq_enrollments');
            $table->index(['section_id', 'status'], 'idx_enrollments_section_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
