<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = require database_path('seeders/data/discounts.php');
        foreach ($discounts as $discount) {
            $discount['created_at'] = Carbon::now();
            DB::table('discounts')->insert($discount);
        }
    }
}
