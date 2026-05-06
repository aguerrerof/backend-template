<?php

namespace App\Http\Controllers;

use App\Http\Formatters\ApiResponseFormatter;
use App\Http\Requests\StoreActivityLogRequest;
use App\Models\ActivityLog;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogsController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int)$request->query('perPage', 5);

        $builder = ActivityLog::query()
            ->orderBy('created_at', 'DESC');

        if ($search = trim($request->query('q', ''))) {
            $term = "%{$search}%";
            $builder->where(function ($query) use ($term) {
                $query
                    ->orWhereRaw('"level"::text ILIKE ?', [$term])
                    ->orWhereRaw('"message"::text ILIKE ?', [$term]);
            });
        }

        if ($request->filled(['from', 'to'])) {
            try {
                $from = Carbon::parse($request->query('from'))->startOfDay();
                $to = Carbon::parse($request->query('to'))->endOfDay();
                if ($from->lte($to)) {
                    $builder->whereBetween('created_at', [$from, $to]);
                }
            } catch (Exception $e) {
                $errors = ['date_range' => __('custom.invalid-date-range-selected')];
            }
        }
        $logs = $builder
            ->paginate(
                in_array($perPage, [5, 10])
                    ? $perPage
                    : 5,
            )
            ->withQueryString();
        return view('activity-logs', compact('logs', 'perPage'))
            ->withErrors($errors ?? null);
    }

    public function store(StoreActivityLogRequest $request): JsonResponse
    {
        ActivityLog::query()->createOrFirst([
            'level' => $request->validated()['level'],
            'message' => $request->validated()['message'],
            'context' => json_encode($request->validated()['context']),
            'created_at' => Carbon::now(),
        ]);
        return ApiResponseFormatter::formatSuccess([], 'Ok', Response::HTTP_CREATED);
    }
}
