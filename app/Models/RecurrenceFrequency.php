<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $value
 */
class RecurrenceFrequency extends Model
{
    protected $table = 'recurrence_frequency';

    protected $fillable = [
        'is_default',
        'name',
        'value',
    ];

    public static function getFrequency(string $name): ?self
    {
        return self::where('name', $name)->first();
    }

}
