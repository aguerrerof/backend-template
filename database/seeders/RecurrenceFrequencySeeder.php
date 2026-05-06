<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecurrenceFrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frequencies = require database_path('seeders/data/recurrence_frequencies.php');
        foreach ($frequencies as $frequency) {
            DB::table('recurrence_frequency')->insert($frequency);
        }
    }
}
