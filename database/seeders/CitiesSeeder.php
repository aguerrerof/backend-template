<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = require database_path('seeders/data/cities.php');
        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'name' => $city,
                'created_at' => now(),
                'updated_at' => null,
            ]);
        }
    }
}
