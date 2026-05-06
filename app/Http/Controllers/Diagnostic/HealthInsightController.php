<?php

namespace App\Http\Controllers\Diagnostic;

use App\Http\Controllers\Controller;
use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\AnalyzeHealthQuizRequest;
use App\Services\Diagnostic\AnimalHealthInsightService;

class HealthInsightController extends Controller
{
    public function __construct(private readonly AnimalHealthInsightService $animalHealthInsightService)
    {
    }

    public function analyze(AnalyzeHealthQuizRequest $request)
    {
        $result = $this->animalHealthInsightService->analyze($request->getQuizPayload());
        return ApiResponseFormatter::formatSuccess(
            [
                'cached' => isset($result['cached']),
                'output' => $result,
            ],
            'Ok',
        );
    }
}
