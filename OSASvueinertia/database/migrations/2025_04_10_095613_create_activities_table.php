<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_application_id')->constrained()->onDelete('cascade'); // foreign key to OrganizationApplication
            $table->string('objective');
            $table->string('name');
            $table->string('description');
            $table->string('persons_involved');
            $table->date('target_date');
            $table->decimal('budget', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
};
