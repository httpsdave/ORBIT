<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert the new image upload setting
        SystemSetting::updateOrCreate(
            ['key' => 'allow_image_uploads'],
            [
                'value' => '1', // Default to enabled
                'description' => 'Allow image uploads in List of Members and List of Officers forms'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the setting
        SystemSetting::where('key', 'allow_image_uploads')->delete();
    }
};
