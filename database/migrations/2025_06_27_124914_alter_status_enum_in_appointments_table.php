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
        // Note: Doctrine DBAL is required for modifying enum columns in Laravel
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Started', 'In Progress', 'Ended', 'Cancelled'])->default('Pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            //
        });
    }
};
