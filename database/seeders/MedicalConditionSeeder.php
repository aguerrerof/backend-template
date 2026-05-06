<?php

namespace Database\Seeders;

use App\Models\MedicalCondition;
use Illuminate\Database\Seeder;

class MedicalConditionSeeder extends Seeder
{
    public function run(): void
    {
        MedicalCondition::truncate();

        MedicalCondition::insert([
            ['name' => 'Diabetes', 'is_other' => false, 'active' => true],
            ['name' => 'Alergias', 'is_other' => false, 'active' => true],
            ['name' => 'Problemas renales', 'is_other' => false, 'active' => true],
            ['name' => 'Problemas cardíacos', 'is_other' => false, 'active' => true],
            ['name' => 'Otros', 'is_other' => true, 'active' => true],
        ]);
    }
}
