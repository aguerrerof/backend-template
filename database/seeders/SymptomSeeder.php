<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    public function run(): void
    {
        Symptom::truncate();

        Symptom::insert([
            // GENERALES
            [
                'name' => 'Pérdida de apetito',
                'category' => 'General',
                'species_key' => null,
                'is_other' => false,
                'active' => true,
            ],
            [
                'name' => 'Letargo / cansancio',
                'category' => 'General',
                'species_key' => null,
                'is_other' => false,
                'active' => true,
            ],
            [
                'name' => 'Fiebre',
                'category' => 'General',
                'species_key' => null,
                'is_other' => false,
                'active' => true,
            ],

            // DIGESTIVOS
            [
                'name' => 'Vómitos',
                'category' => 'Digestivo',
                'species_key' => null,
                'is_other' => false,
                'active' => true,
            ],
            [
                'name' => 'Diarrea',
                'category' => 'Digestivo',
                'species_key' => null,
                'is_other' => false,
                'active' => true,
            ],

            // RESPIRATORIOS
            [
                'name' => 'Tos',
                'category' => 'Respiratorio',
                'species_key' => 'dog',
                'is_other' => false,
                'active' => true,
            ],

            // COMPORTAMIENTO (GATOS)
            [
                'name' => 'Se esconde más de lo normal',
                'category' => 'Comportamiento',
                'species_key' => 'cat',
                'is_other' => false,
                'active' => true,
            ],

            // OTROS
            [
                'name' => 'Otros síntomas',
                'category' => 'General',
                'species_key' => null,
                'is_other' => true,
                'active' => true,
            ],
        ]);
    }
}
