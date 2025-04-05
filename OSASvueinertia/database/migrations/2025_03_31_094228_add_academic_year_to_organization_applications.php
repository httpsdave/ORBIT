<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('organization_applications', function (Blueprint $table) {
        $table->string('academic_year_start')->nullable();
        $table->string('academic_year_end')->nullable();
    });
}

public function down()
{
    Schema::table('organization_applications', function (Blueprint $table) {
        $table->dropColumn(['academic_year_start', 'academic_year_end']);
    });
}

};
