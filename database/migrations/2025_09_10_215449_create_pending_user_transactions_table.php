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
        Schema::create('pending_user_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->string('type')->nullable();
            $table->string('transaction_id')->unique();
            $table->string('payload')->nullable();
            $table->string('symmetric_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_user_transactions');
    }
};
