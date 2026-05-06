<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return SettingResource::formatCollectionToApiResponse(
            Setting::query()
                ->whereLike('key', "%{$request->get('q')}%")
                ->orWhereLike('value', "%{$request->get('q')}%")
                ->orWhereLike('type', "%{$request->get('q')}%")
                ->paginate(),
            'Ok'
        );
    }
}
