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
        Schema::create('customer_billing_information', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('identification')->index();
            $table->string('type');
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_billing_information');
    }
};
