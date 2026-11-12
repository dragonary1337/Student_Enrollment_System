<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('name');
            $table->string('first_name', 50)->nullable()->after('password');
            $table->string('last_name', 50)->nullable()->after('first_name');
            $table->enum('role', ['Admin', 'Operator'])->default('Operator')->after('last_name');
            $table->enum('status', ['Pending', 'Active', 'Inactive'])->default('Pending')->after('role');
            $table->timestamp('last_login')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'first_name', 'last_name', 'role', 'status', 'last_login']);
        });
    }
};
