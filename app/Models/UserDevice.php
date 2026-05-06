<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $table = 'user_devices';
    protected $fillable = [
        'shopify_id',
        'device_id',
        'firebase_token',
    ];
}
