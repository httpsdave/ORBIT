<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->string('activity_title')->nullable()->after('activity_date');
            $table->string('venue')->nullable()->after('activity_title');
            $table->string('date')->nullable()->after('venue');
            $table->string('time')->nullable()->after('date');
        });
    }

    public function down()
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn(['activity_title', 'venue', 'date', 'time']);
        });
    }
}; 