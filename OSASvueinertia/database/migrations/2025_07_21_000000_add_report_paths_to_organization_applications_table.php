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
            $table->string('accomplishment_report_path')->nullable();
            $table->string('narrative_report_path')->nullable();
            $table->string('bylaws_path')->nullable();
            $table->string('financial_report_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn([
                'accomplishment_report_path',
                'narrative_report_path',
                'bylaws_path',
                'financial_report_path',
            ]);
        });
    }
}; 