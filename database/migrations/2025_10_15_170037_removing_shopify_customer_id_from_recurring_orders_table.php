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
        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->dropColumn('shopify_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->string('shopify_customer_id')->nullable();
        });
    }
};
