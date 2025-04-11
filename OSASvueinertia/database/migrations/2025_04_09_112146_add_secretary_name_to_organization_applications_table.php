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
        $table->string('secretary_name')->nullable()->after('form_date');
    });
}

public function down(): void
{
    Schema::table('organization_applications', function (Blueprint $table) {
        $table->dropColumn('secretary_name');
    });
}

};
