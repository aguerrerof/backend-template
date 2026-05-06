<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecurrenceFrequencyResource;
use App\Models\RecurrenceFrequency;
use Illuminate\Http\JsonResponse;

class RecurrenceFrequenciesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return RecurrenceFrequencyResource::formatCollectionToApiResponse(
            RecurrenceFrequency::query()->paginate(),
            null,
        );
    }

}
