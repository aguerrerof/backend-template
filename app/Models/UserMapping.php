<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMapping extends Model
{
    protected $table = 'user_mappings';
    protected $fillable = [
        'shopify_user_id',
        'firebase_id',
    ];
    public $timestamps = false;
}
