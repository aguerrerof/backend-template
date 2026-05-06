<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $key
 * @property string $value
 * @property string $type
 */
class Setting extends Model
{
    use SoftDeletes;

    protected $table = 'settings';
    protected $fillable = [
        'key',
        'value',
        'type'
    ];

    public function getCastValueAttribute()
    {
        return match ($this->type) {
            'int' => (int) $this->value,
            'float' => (float) $this->value,
            'bool' => filter_var($this->value, FILTER_VALIDATE_BOOL),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

}
