<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;

    protected $table = 'discounts';
    protected $fillable = [
        'code',
        'rule_id',
        'title',
        'enabled'.
        'starts_at',
        'ends_at',
        'usage_count',
        'usage_limit',
        'value',
        'value_type',
    ];

}
