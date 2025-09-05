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
            $table->string('second_adviser_prefix')->nullable();
            $table->string('second_adviser_suffix')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn(['second_adviser_prefix', 'second_adviser_suffix']);
        });
    }
};
