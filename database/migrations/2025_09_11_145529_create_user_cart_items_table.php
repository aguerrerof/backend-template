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
        Schema::create('user_cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->string('variant_id')->nullable();
            $table->string('title');
            $table->string('flavor')->nullable();
            $table->string('size')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('apply_discount')->default(false);
            $table->boolean('apply_tax')->default(false);
            $table->boolean('is_recurrence')->default(false);
            $table->string('frequency')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10);
            $table->timestamp('added_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cart_items');
    }
};
