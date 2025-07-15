<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->json('ratings')->nullable()->after('president_name');
        });
    }

    public function down()
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn('ratings');
        });
    }
}; 