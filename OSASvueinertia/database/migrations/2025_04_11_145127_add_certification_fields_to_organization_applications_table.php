<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->string('student_name')->nullable();
            $table->string('course_year_section')->nullable();
            $table->string('position_rank')->nullable();
            $table->boolean('is_bonafide')->default(false);
            $table->boolean('is_not_academic_probation')->default(false);
            $table->boolean('is_not_disciplinary_probation')->default(false);
            $table->boolean('has_position')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn([
                'student_name',
                'course_year_section',
                'position_rank',
                'is_bonafide',
                'is_not_academic_probation',
                'is_not_disciplinary_probation',
                'has_position',
            ]);
        });
    }
};
