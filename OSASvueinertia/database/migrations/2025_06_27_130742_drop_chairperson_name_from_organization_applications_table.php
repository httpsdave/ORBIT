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
            // Drop chairperson_name column since we're now using director_name
            if (Schema::hasColumn('organization_applications', 'chairperson_name')) {
                $table->dropColumn('chairperson_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            // Add back chairperson_name column if needed to rollback
            if (!Schema::hasColumn('organization_applications', 'chairperson_name')) {
                $table->string('chairperson_name')->nullable()->after('academic_year_end');
            }
        });
    }
};
