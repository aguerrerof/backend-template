<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_user_id')->index();
            $table->string('firebase_id')->index();
            $table->timestamps();
            $table->unique(['shopify_user_id', 'firebase_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mappings');
    }
};
