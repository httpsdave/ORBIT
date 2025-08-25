<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize default system settings for form defaults
        SystemSetting::updateOrCreate(
            ['key' => 'coordinator_name'],
            [
                'value' => '',
                'description' => 'Default coordinator name for forms'
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'director_name'],
            [
                'value' => '',
                'description' => 'Default director name for forms'
            ]
        );
    }
}
