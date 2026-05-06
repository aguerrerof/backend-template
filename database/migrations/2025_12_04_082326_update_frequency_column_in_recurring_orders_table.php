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
        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('recurrence_frequency_id')->nullable();
        });

        $rows = DB::table('recurring_orders')->select('id', 'frequency')->get();

        foreach ($rows as $row) {
            $freq = DB::table('recurrence_frequency')
                ->where('name', '=', $row->frequency)
                ->first();

            if ($freq) {
                DB::table('recurring_orders')
                    ->where('id', $row->id)
                    ->update(['recurrence_frequency_id' => $freq->id]);
            }
        }

        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('recurrence_frequency_id')->nullable(false)->change();
        });

        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->dropColumn('frequency');
        });

        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->foreign('recurrence_frequency_id')
                ->references('id')
                ->on('recurrence_frequency');
        });
    }

    public function down(): void
    {
        $constraint = DB::selectOne("
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'recurring_orders'
              AND constraint_type = 'FOREIGN KEY'
              AND constraint_name LIKE '%recurrence_frequency_id%'
        ");

        if ($constraint) {
            Schema::table('recurring_orders', function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint->constraint_name);
            });
        }

        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->string('frequency')->nullable();
        });

        $rows = DB::table('recurring_orders')->select('id', 'recurrence_frequency_id')->get();

        foreach ($rows as $row) {
            $freq = DB::table('recurrence_frequency')
                ->where('id', $row->recurrence_frequency_id)
                ->first();

            if ($freq) {
                DB::table('recurring_orders')
                    ->where('id', $row->id)
                    ->update(['frequency' => $freq->name]);
            }
        }

        Schema::table('recurring_orders', function (Blueprint $table) {
            $table->dropColumn('recurrence_frequency_id');
        });
    }
};
