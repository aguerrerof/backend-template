<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = require database_path('seeders/data/shipping_rates.php');
        foreach ($rates as $rate) {
            DB::table('shipping_rates')->insert([
                'code' => $rate['code'],
                'identifier' => $rate['identifier'],
                'title' => $rate['title'],
                'price' => $rate['price'],
                'created_at' => Carbon::now(),
            ]);
        }
    }
}
