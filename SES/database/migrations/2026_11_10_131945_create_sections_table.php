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
        Schema::create('sections', function (Blueprint $table) {
            $table->id('section_id');
            $table->string('section_code', 10);
            $table->foreignId('subject_id')->constrained('subjects', 'subject_id')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('instructor_id')->nullable()->constrained('instructors', 'instructor_id')->nullOnDelete()->cascadeOnUpdate();
            $table->string('school_year', 9);
            $table->enum('semester', ['1st', '2nd', 'Summer']);
            $table->string('schedule_day', 10)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room', 10)->nullable();
            $table->unsignedInteger('max_capacity')->default(40);
            $table->unique(['section_code', 'school_year', 'semester'], 'uq_sections_term');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE sections ADD CONSTRAINT chk_sections_capacity CHECK (max_capacity > 0)');
            DB::statement('ALTER TABLE sections ADD CONSTRAINT chk_sections_time CHECK (start_time IS NULL OR end_time IS NULL OR end_time > start_time)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
