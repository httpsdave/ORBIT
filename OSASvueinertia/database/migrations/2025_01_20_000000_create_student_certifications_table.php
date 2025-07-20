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
        Schema::create('student_certifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_application_id');
            $table->string('student_name');
            $table->string('course_year_section');
            $table->string('position_rank')->nullable();
            $table->boolean('is_bonafide')->default(false);
            $table->boolean('is_not_academic_probation')->default(false);
            $table->boolean('is_not_disciplinary_probation')->default(false);
            $table->boolean('has_position')->default(false);
            $table->date('certification_date');
            $table->timestamps();

            $table->foreign('organization_application_id')
                  ->references('id')
                  ->on('organization_applications')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_certifications');
    }
}; 