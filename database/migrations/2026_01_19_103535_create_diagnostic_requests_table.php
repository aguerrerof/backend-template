<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diagnostic_requests', function (Blueprint $table) {
            $table->id();
            $table->string('species_key');
            $table->foreignId('breed_id')->nullable();
            $table->string('sex');
            $table->boolean('sterilized');
            $table->jsonb('medical_conditions');
            $table->jsonb('symptoms');
            $table->text('other_symptoms')->nullable();
            $table->jsonb('ai_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostic_requests');
    }
};
