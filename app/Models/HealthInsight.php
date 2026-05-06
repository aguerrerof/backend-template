<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthInsight extends Model
{
    protected $table = 'health_insights';
    protected $fillable = [
        'request_hash',
        'quiz_payload',
        'ai_response',
        'model',
        'tokens_used',
    ];

    protected $casts = [
        'quiz_payload' => 'array',
        'ai_response' => 'array',
    ];
}
