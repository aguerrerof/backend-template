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
        Schema::table('deleted_users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['shopify_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deleted_users', function (Blueprint $table) {
            $table->unique('email');
            $table->unique('shopify_id');
        });
    }
};
