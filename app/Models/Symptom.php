<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Symptom extends Model
{
    protected $fillable = [
        'name',
        'category',
        'species_key',
        'is_other',
        'active',
    ];
}
