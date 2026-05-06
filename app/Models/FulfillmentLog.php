<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FulfillmentLog extends Model
{
    protected $table = 'fulfillment_logs';

    protected $fillable = [
        'fulfillment_id',
        'user_id',
        'changes',
        'description',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function fulfillment(): BelongsTo
    {
        return $this->belongsTo(Fulfillment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
