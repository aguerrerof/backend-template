<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = 'order_payments';
    protected $fillable = [
        'order_id',
        'status' ,// pending, authorized, failed, draft
        'message',
        'details',
        'processed_at',
    ];
    protected $casts = [
      'details' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class, 'order_payment_id');
    }

    /**
     * Acceso directo a la última factura generada
     */
    public function latestBilling()
    {
        return $this->hasOne(Billing::class)->latestOfMany();
    }
}
