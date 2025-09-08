<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class CleanMissingProfilePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clean-missing-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up profile photo paths for files that no longer exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for missing profile photos...');
        
        $users = User::whereNotNull('profile_photo_path')->get();
        $cleaned = 0;
        
        foreach ($users as $user) {
            if (!Storage::disk('public')->exists($user->profile_photo_path)) {
                $this->warn("Missing file: {$user->profile_photo_path} for user {$user->name}");
                $user->update(['profile_photo_path' => null]);
                $cleaned++;
            }
        }
        
        $this->info("Cleaned {$cleaned} missing profile photo paths.");
        return Command::SUCCESS;
    }
}
