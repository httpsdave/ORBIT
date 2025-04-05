<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->string('status')->default('Pending'); // Add the missing column
        });
    }

    public function down() {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn('status'); // Rollback if needed
        });
    }
};

