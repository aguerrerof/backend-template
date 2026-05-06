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
        Schema::table('logistic_providers', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropColumn('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistic_providers', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->boolean('active')->default(true);
        });
    }
};
