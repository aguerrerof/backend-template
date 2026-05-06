<?php

namespace App\Services\Diagnostic;

use App\Models\Domain\QuizPayload;

interface AnimalHealthInsightService
{
    public function analyze(QuizPayload $quiz): array;
}
