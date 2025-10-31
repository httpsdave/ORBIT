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
            // Update the enum to include the Status Report type
            DB::statement("ALTER TABLE activity_reports MODIFY COLUMN report_type ENUM('LSPU-OSAS-SF-FINANCIAL', 'LSPU-OSAS-SF-NARRATIVE', 'LSPU-OSAS-SF-ACCOMPLISHMENT', 'LSPU-OSAS-SF-EVAL', 'LSPU-OSAS-SF-009', 'LSPU-OSAS-SF-STATUS-REPORT')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_reports', function (Blueprint $table) {
            // Revert back to previous enum values (remove Status Report)
            DB::statement("ALTER TABLE activity_reports MODIFY COLUMN report_type ENUM('LSPU-OSAS-SF-FINANCIAL', 'LSPU-OSAS-SF-NARRATIVE', 'LSPU-OSAS-SF-ACCOMPLISHMENT', 'LSPU-OSAS-SF-EVAL', 'LSPU-OSAS-SF-009')");
        });
    }
};
