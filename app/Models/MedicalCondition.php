<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalCondition extends Model
{
    protected $table = 'medical_conditions';

    protected $fillable = ['name', 'is_other', 'active'];
}
