<?php

namespace App\Http\Controllers;

use App\Http\Formatters\ApiResponseFormatter;
use App\Models\Breed;
use App\Models\MedicalCondition;
use App\Models\Species;
use App\Models\Symptom;

class CatalogController extends Controller
{
    public function species()
    {
        $data = Species::select('id', 'key', 'name')
            ->orderBy('name')
            ->get();
        return ApiResponseFormatter::formatSuccess(
            $data->toArray(),
            'Ok',
        );
    }

    public function breeds(string $speciesKey)
    {
        $species = Species::where('key', $speciesKey)->firstOrFail();

        $data =  Breed::where('species_id', $species->id)
            ->select('id', 'name', 'is_other')
            ->orderBy('is_other')
            ->orderBy('name')
            ->get();
        return ApiResponseFormatter::formatSuccess(
            $data->toArray(),
            'Ok',
        );
    }

    public function symptoms(string $speciesKey)
    {
        $data = Symptom::where('active', true)
            ->where(function ($query) use ($speciesKey) {
                $query->whereNull('species_key')
                    ->orWhere('species_key', $speciesKey);
            })
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        return ApiResponseFormatter::formatSuccess(
            $data->toArray(),
            'Ok',
        );
    }

    public function medicalConditions()
    {
        $data = MedicalCondition::where('active', true)
            ->select('id', 'name', 'is_other')
            ->orderBy('is_other')
            ->orderBy('name')
            ->get();
        return ApiResponseFormatter::formatSuccess(
            $data->toArray(),
            'Ok',
        );
    }
}
