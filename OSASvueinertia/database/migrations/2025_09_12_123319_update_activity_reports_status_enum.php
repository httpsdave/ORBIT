<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_reports', function (Blueprint $table) {
            // First update any existing data to match new enum values
            DB::statement("UPDATE activity_reports SET status = 'Pending' WHERE status = 'pending'");
            DB::statement("UPDATE activity_reports SET status = 'Approved' WHERE status = 'approved'");
            DB::statement("UPDATE activity_reports SET status = 'Disapproved' WHERE status IN ('rejected', 'disapproved')");
            DB::statement("UPDATE activity_reports SET status = 'Pending' WHERE status = 'submitted'");
            
            // Change the enum to new values
            DB::statement("ALTER TABLE activity_reports MODIFY status ENUM('Pending', 'Approved', 'Disapproved') DEFAULT 'Pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_reports', function (Blueprint $table) {
            // Revert back to original enum values
            DB::statement("UPDATE activity_reports SET status = 'pending' WHERE status = 'Pending'");
            DB::statement("UPDATE activity_reports SET status = 'approved' WHERE status = 'Approved'");
            DB::statement("UPDATE activity_reports SET status = 'rejected' WHERE status = 'Disapproved'");
            
            DB::statement("ALTER TABLE activity_reports MODIFY status ENUM('pending', 'submitted', 'approved', 'rejected') DEFAULT 'pending'");
        });
    }
};
