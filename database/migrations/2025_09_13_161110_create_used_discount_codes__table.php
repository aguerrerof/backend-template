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
        Schema::create('used_discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->foreignId('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Evita que un usuario use el mismo código más de una vez
            //            $table->unique(['user_id', 'promo_code_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('used_promo_codes_');
    }
};
