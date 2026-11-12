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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('school_year', 9);
            $table->enum('semester', ['1st', '2nd', 'Summer']);
            $table->decimal('amount', 10, 2);
            $table->foreignId('payment_method_id')->constrained('payment_methods', 'payment_method_id')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamp('payment_datetime')->useCurrent();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_amount CHECK (amount > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
