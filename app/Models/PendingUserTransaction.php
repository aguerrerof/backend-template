<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $user_id
 * @property string $type
 * @property string $transaction_id
 * @property array $payload
 * @property string $symmetric_key
 */
class PendingUserTransaction extends Model
{
    protected $table = 'pending_user_transactions';
    protected $fillable = [
        'user_id',
        'type',
        'transaction_id',
        'payload',
        'symmetric_key'
    ];
}
