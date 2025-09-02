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
            $table->string('adviser_prefix')->nullable()->after('adviser_name');
            $table->string('adviser_suffix')->nullable()->after('adviser_prefix');
            $table->string('dean_prefix')->nullable()->after('dean_name');
            $table->string('dean_suffix')->nullable()->after('dean_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn(['adviser_prefix', 'adviser_suffix', 'dean_prefix', 'dean_suffix']);
        });
    }
};
