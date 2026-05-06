<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $identification
 * @property string $type
 * @property string $phone
 * @property boolean $is_default
 */
class CustomerBillingInformation extends Model
{
    use SoftDeletes;
    protected $table = 'customer_billing_information';
    protected $fillable = [
        'user_id',
        'email',
        'first_name',
        'last_name',
        'identification',
        'type',
        'phone',
        'is_default',
    ];

    public static function getDefaultByUser(string $userId): ?CustomerBillingInformation
    {
        return self::query()
            ->where('user_id', $userId)
            ->where('is_default', true)
            ->first();
    }
}
