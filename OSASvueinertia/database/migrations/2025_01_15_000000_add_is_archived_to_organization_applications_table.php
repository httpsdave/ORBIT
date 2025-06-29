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
            $table->boolean('is_archived')->default(false)->after('status');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
            $table->string('academic_year_archived')->nullable()->after('archived_by');
            
            // Add foreign key for archived_by
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['is_archived', 'archived_at', 'archived_by', 'academic_year_archived']);
        });
    }
}; 