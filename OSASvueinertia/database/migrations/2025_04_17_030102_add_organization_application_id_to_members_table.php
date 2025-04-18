<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('members', function (Blueprint $table) {
        $table->unsignedBigInteger('organization_application_id')->nullable();
        $table->foreign('organization_application_id')->references('id')->on('organization_applications')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('members', function (Blueprint $table) {
        $table->dropForeign(['organization_application_id']);
        $table->dropColumn('organization_application_id');
    });
}

};
