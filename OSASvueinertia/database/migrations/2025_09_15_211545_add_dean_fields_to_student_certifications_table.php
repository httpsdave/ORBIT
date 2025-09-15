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
        Schema::table('student_certifications', function (Blueprint $table) {
            $table->string('dean_name')->nullable()->after('certification_date');
            $table->string('dean_prefix')->nullable()->after('dean_name');
            $table->string('dean_suffix')->nullable()->after('dean_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_certifications', function (Blueprint $table) {
            $table->dropColumn(['dean_name', 'dean_prefix', 'dean_suffix']);
        });
    }
};
