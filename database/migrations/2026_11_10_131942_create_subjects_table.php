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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subject_id');
            $table->string('subject_code', 10)->unique();
            $table->string('subject_name', 100);
            $table->decimal('units', 2, 1);
            $table->foreignId('department_id')->nullable()->constrained('departments', 'department_id')->nullOnDelete()->cascadeOnUpdate();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE subjects ADD CONSTRAINT chk_subjects_units CHECK (units > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
