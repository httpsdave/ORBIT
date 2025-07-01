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
        Schema::create('organization_applications', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('president_name');
            $table->date('application_date');
            $table->text('requirements')->nullable();
            $table->string('adviser_name')->nullable();
            $table->string('dean_name')->nullable();
            $table->string('coordinator_name')->nullable();
            $table->string('director_name')->nullable(); // Added director column
            $table->string('status')->default('Pending'); // Pending, Approved, Disapproved
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_applications');
    }
};
