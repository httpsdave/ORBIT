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
        Schema::table('activities', function (Blueprint $table) {
            // Change budget column to DECIMAL with higher precision
            // DECIMAL(15,2) allows for values up to 9,999,999,999,999.99
            $table->decimal('budget', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Revert back to original type if needed
            // Adjust this based on your original column type
            $table->decimal('budget', 10, 2)->change();
        });
    }
};