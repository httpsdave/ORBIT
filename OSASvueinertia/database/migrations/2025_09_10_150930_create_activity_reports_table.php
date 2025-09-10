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
        Schema::create('activity_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_application_id')->constrained()->onDelete('cascade');
            $table->integer('activity_page_number'); // Which activity page this report is for (1, 2, 3, etc.)
            $table->enum('report_type', ['LSPU-OSAS-SF-FINANCIAL', 'LSPU-OSAS-SF-NARRATIVE', 'LSPU-OSAS-SF-ACCOMPLISHMENT']);
            $table->string('file_path')->nullable(); // Path to uploaded file
            $table->string('original_filename')->nullable(); // Original name of uploaded file
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('feedback')->nullable(); // Admin feedback
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            
            // Ensure one report per activity page per report type
            $table->unique(['organization_application_id', 'activity_page_number', 'report_type'], 'unique_report_per_activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_reports');
    }
};
