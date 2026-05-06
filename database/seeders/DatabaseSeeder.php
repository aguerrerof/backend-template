<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CitiesSeeder::class);
        $this->call(ShippingRateSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(DiscountsSeeder::class);
        $this->call(RecurrenceFrequencySeeder::class);
        $this->call(LogisticProvidersSeeder::class);

    }
}
