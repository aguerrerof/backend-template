<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $table = 'billings';
    protected $fillable = [
          'order_payment_id',
          'invoice_number',
          'access_key',
          'status',
          'total',
          'external_response'
      ];

    protected $casts = [
        'external_response' => 'array',
    ];

    public function orderPayment()
    {
        return $this->belongsTo(OrderPayment::class);
    }
}
