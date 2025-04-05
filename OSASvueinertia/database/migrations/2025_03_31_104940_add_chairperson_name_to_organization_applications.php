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
            $table->string('chairperson_name')->nullable()->after('academic_year_end');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn('chairperson_name');
        });
    }
    
};
