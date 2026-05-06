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
            $table->json('tracking_info')->nullable()->after('delivered_at')->comment('Información completa de tracking y estados del webhook');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fulfillments', function (Blueprint $table) {
            $table->dropColumn('tracking_info');

        });
    }
};
