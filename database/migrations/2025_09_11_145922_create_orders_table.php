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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->unsignedBigInteger('shopify_order_id')->unique();
            $table->string('source')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('recurring_id')->nullable();
            $table->string('notes')->nullable();
            $table->timestamp('created_at_shopify')->nullable();
            $table->json('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
