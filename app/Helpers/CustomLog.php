<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class CustomLog
{
    public static function saveLog(string $level, string $message, array $context): void
    {
        ActivityLog::create([
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
