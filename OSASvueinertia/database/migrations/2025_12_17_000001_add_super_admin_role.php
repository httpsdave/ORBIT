<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if super_admin role already exists
        $superAdminExists = Role::where('slug', 'super_admin')->exists();
        
        if (!$superAdminExists) {
            Role::create([
                'name' => 'Super Administrator',
                'slug' => 'super_admin'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('slug', 'super_admin')->delete();
    }
};
