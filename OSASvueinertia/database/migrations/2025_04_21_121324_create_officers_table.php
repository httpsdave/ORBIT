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
        Schema::create('officers', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('position');
            $table->string('student_number');
            $table->string('photo_path')->nullable(); // if photo is optional
            $table->unsignedBigInteger('organization_application_id');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('organization_application_id')
                  ->references('id')
                  ->on('organization_applications')
                  ->onDelete('cascade');
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('officers');
    }
};
