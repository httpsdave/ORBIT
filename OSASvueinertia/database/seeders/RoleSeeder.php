<?php

// database/seeders/RoleSeeder.php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'Super Administrator', 'slug' => 'super_admin']);
        Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        Role::create(['name' => 'User', 'slug' => 'user']);
    }
}
