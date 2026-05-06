<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('recurring_order_payments', 'order_payments');
        DB::statement('
            ALTER TABLE order_payments
            DROP CONSTRAINT IF EXISTS order_payments_recurring_order_id_foreign
        ');
        DB::statement('
            ALTER TABLE order_payments
            DROP CONSTRAINT IF EXISTS recurring_order_payments_recurring_order_id_foreign
        ');
        Schema::table('order_payments', function (Blueprint $table) {
            $table->renameColumn('recurring_order_id', 'order_id');
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->renameColumn('order_id', 'recurring_order_id');
        });
        Schema::table('order_payments', function (Blueprint $table) {
            $table->foreign('recurring_order_id')
                ->references('id')
                ->on('recurring_orders')
                ->onDelete('cascade');
        });
        Schema::rename('order_payments', 'recurring_order_payments');
    }
};
