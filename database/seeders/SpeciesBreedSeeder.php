<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesBreedSeeder extends Seeder
{
    public function run(): void
    {
        Breed::truncate();
        Species::truncate();

        $dog = Species::create([
            'key' => 'dog',
            'name' => 'Perro',
        ]);

        $cat = Species::create([
            'key' => 'cat',
            'name' => 'Gato',
        ]);

        Breed::insert([
            ['species_id' => $dog->id, 'name' => 'Labrador Retriever', 'is_other' => false],
            ['species_id' => $dog->id, 'name' => 'Pastor Alemán', 'is_other' => false],
            ['species_id' => $dog->id, 'name' => 'Mestizo', 'is_other' => false],
            ['species_id' => $dog->id, 'name' => 'Otros', 'is_other' => true],
            ['species_id' => $cat->id, 'name' => 'Siamés', 'is_other' => false],
            ['species_id' => $cat->id, 'name' => 'Persa', 'is_other' => false],
            ['species_id' => $cat->id, 'name' => 'Mestizo', 'is_other' => false],
            ['species_id' => $cat->id, 'name' => 'Otros', 'is_other' => true],
        ]);
    }
}
