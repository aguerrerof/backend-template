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
        Schema::table('fulfillments', function (Blueprint $table) {
            $table->decimal('total_weight', 10)->default(0)->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fulfillments', function (Blueprint $table) {
            $table->dropColumn('total_weight');
        });
    }
};
