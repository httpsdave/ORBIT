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
            $table->string('semester')->nullable();
            $table->string('second_adviser')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn(['semester', 'second_adviser']);
        });
    }
};
