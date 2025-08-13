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
            $table->string('signed_document_link')->nullable()->after('signed_document_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropColumn('signed_document_link');
        });
    }
};
