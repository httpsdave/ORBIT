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
        $table->unsignedBigInteger('reviewed_by')->nullable()->after('feedback');
        $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

        // If you want to enforce a foreign key (optional):
        $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('organization_applications', function (Blueprint $table) {
        if (Schema::hasColumn('organization_applications', 'reviewed_by')) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn('reviewed_by');
        }
        if (Schema::hasColumn('organization_applications', 'reviewed_at')) {
            $table->dropColumn('reviewed_at');
        }
    });
}

};
