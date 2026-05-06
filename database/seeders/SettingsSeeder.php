<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = require database_path('seeders/data/settings.php');
        foreach ($rates as $rate) {
            DB::table('settings')->insert([
                'key' => $rate['key'],
                'value' => $rate['value'],
                'type' => $rate['type'],
                'created_at' => Carbon::now(),
            ]);
        }
    }
}
