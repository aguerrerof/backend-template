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
        if (!Schema::hasTable('support_tickets')) {
            return;
        }

        if (!Schema::hasColumn('support_tickets', 'guest_name')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->string('guest_name', 150)->nullable()->after('user_id');
            });
        }

        if (!Schema::hasColumn('support_tickets', 'guest_email')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->string('guest_email', 190)->nullable()->after('guest_name');
            });
        }

        // Make `user_id` optional so external users can submit tickets.
        try {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
            // Ignore if the foreign key doesn't exist yet.
        }

        try {
            DB::statement('ALTER TABLE support_tickets ALTER COLUMN user_id DROP NOT NULL');
        } catch (\Throwable $e) {
            // Ignore if the column is already nullable.
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('support_tickets')) {
            return;
        }

        try {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('support_tickets', 'guest_email')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropColumn('guest_email');
            });
        }

        if (Schema::hasColumn('support_tickets', 'guest_name')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropColumn('guest_name');
            });
        }

        try {
            DB::statement('ALTER TABLE support_tickets ALTER COLUMN user_id SET NOT NULL');
        } catch (\Throwable $e) {
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
