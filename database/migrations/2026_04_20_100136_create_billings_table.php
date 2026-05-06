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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_payment_id')
                  ->constrained('order_payments')
                  ->onDelete('cascade');
            $table->unique('order_payment_id');
            $table->string('invoice_number')->nullable()->index();
            $table->string('access_key', 50)->nullable()->index();
            $table->string('status')->default('pending');
            $table->decimal('total', 12, 2)->default(0);
            $table->json('external_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
