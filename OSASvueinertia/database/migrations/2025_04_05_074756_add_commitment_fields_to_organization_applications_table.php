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
        $table->string('adviser_signature')->nullable();
        $table->string('adviser_college')->nullable();
        $table->string('adviser_rank')->nullable();
        $table->string('adviser_address')->nullable();
        $table->string('adviser_contact')->nullable();
        $table->date('form_date')->nullable();
    });
}

public function down()
{
    Schema::table('organization_applications', function (Blueprint $table) {
        $table->dropColumn([
            'adviser_signature',
            'adviser_college',
            'adviser_rank',
            'adviser_address',
            'adviser_contact',
            'form_date',
        ]);
    });
}

};
