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
        DB::statement('ALTER TABLE pending_user_transactions ALTER COLUMN payload TYPE json USING payload::json');
        Schema::table('pending_user_transactions', function (Blueprint $table) {
            $table->text('symmetric_key')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE pending_user_transactions ALTER COLUMN payload TYPE varchar(255) USING payload::text');
        Schema::table('pending_user_transactions', function (Blueprint $table) {
            $table->string('symmetric_key', 255)->nullable(false)->change();
        });
    }
};
