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
        Schema::create('autosaved_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('form_type');
            $table->json('form_data');
            $table->timestamps();
            
            // Ensure one autosave record per user per form type
            $table->unique(['user_id', 'form_type']);
            
            // Index for faster queries
            $table->index(['user_id', 'form_type', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autosaved_forms');
    }
};
