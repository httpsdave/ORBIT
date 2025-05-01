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
        // Create colleges table first
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('acronym');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Then create student_orgs table with reference to colleges
        Schema::create('student_orgs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('acronym')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_orgs');
        Schema::dropIfExists('colleges');
    }
};
