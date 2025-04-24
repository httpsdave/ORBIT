<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('attendees', function (Blueprint $table) {
        $table->id();
        $table->foreignId('organization_application_id')->constrained()->onDelete('cascade');
        $table->string('name')->nullable();
        $table->string('course_year_section')->nullable();
        $table->string('signature')->nullable(); // or use binary if storing image data
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
