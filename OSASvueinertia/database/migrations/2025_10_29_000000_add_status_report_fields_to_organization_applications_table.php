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
        Schema::table('organization_applications', function (Blueprint $table) {
            // Make application_date nullable since Activity Status Report uses report_date instead
            $table->date('application_date')->nullable()->change();
            
            // Add report_date for Activity Status Report
            $table->date('report_date')->nullable()->after('application_date');
            
            // Add JSON fields for approved and unapproved activities
            $table->json('approved_activities')->nullable()->after('report_date');
            $table->json('unapproved_activities')->nullable()->after('approved_activities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            // Remove the new fields
            $table->dropColumn(['report_date', 'approved_activities', 'unapproved_activities']);
            
            // Revert application_date to not nullable
            $table->date('application_date')->nullable(false)->change();
        });
    }
};
