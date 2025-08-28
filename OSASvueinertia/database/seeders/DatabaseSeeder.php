<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\College;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the role seeder first
        $this->call([
            RoleSeeder::class,
        ]);
        
        // Create default colleges
        $colleges = [
            ['name' => 'College of Arts and Sciences', 'acronym' => 'CAS'],
            ['name' => 'College of Computer Studies', 'acronym' => 'CCS'],
            ['name' => 'College of Criminal Justice Education', 'acronym' => 'CCJE'],
            ['name' => 'College of Engineering', 'acronym' => 'COE'],
            ['name' => 'College of Industrial Technology', 'acronym' => 'CIT'],
            ['name' => 'College of Teacher Education', 'acronym' => 'CTE'],
            ['name' => 'College of International Hospitality and Tourism Management', 'acronym' => 'CIHTM'],
            ['name' => 'College of Business Administration and Accountancy', 'acronym' => 'CBAA'],
        ];

        foreach ($colleges as $college) {
            College::create($college);
        }
        
        // Create an admin user
        User::create([
            'name' => 'SOU',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // Admin role
            'email_verified_at' => now(),
        ]);
        
        // Create 7 regular users
        for ($i = 1; $i <= 7; $i++) {
            User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'role_id' => 2, // User role
                'email_verified_at' => now(),
            ]);
        }

        // Create welcome notification
        $welcomeNotification = Notification::create([
            'title' => 'Welcome to ORBIT',
            'message' => 'Welcome to ORBIT - Your Student Organization Unit Information System',
            'type' => 'success',
            'is_active' => true,
        ]);

        // Assign the notification to all users (including admin)
        $users = User::all();
        foreach ($users as $user) {
            $user->notifications()->attach($welcomeNotification->id, [
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}