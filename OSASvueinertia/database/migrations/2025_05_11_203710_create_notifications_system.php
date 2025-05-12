<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create notifications table (if not already exists)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('message');
                $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Create user_notifications table (junction table)
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Ensure a user can't have the same notification twice
            $table->unique(['user_id', 'notification_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('notifications');
    }
};
