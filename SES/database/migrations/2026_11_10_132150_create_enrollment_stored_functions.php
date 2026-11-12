<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Kept for migration-history compatibility; advanced database routines are no longer installed.
    }

    public function down(): void
    {
        // The removed database routines are intentionally not restored.
    }
};
