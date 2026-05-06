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
        Schema::create('recurring_orders', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('frequency');
            $table->json('line_items');
            $table->timestamp('next_charge_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_method_id')->nullable();
            $table->json('shipping_address')->nullable();
            $table->string('shopify_customer_id')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->string('status')->nullable();
            $table->string('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_orders');
    }
};
