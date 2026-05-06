<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $orders = DB::table('orders')
            ->whereNotNull('logistic_provider_id')
            ->get();

        foreach ($orders as $order) {
            DB::table('fulfillments')->insert([
                'order_id' => $order->id,
                'logistic_provider_id' => $order->logistic_provider_id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('fulfillments')
            ->where('status', 'pending')
            ->delete();
    }
};
