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
        // Productivity tables (goals, tasks, calendar_entries) are already up to date.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
