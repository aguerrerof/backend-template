<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeletedUser extends Model
{
    use SoftDeletes;
    public $timestamps = false;
    protected $table = 'deleted_users';
    protected $fillable = [
        'email',
        'shopify_id',
        'created_at',
        'updated_at',
    ];
}
