<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCity extends Model
{
    protected $table = 'shipping_cities';
    protected $fillable = ['code', 'name'];
}
