<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunCronRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class SystemOperationsController extends Controller
{
    public function index(): View
    {
        return view('system-operations.administrator');
    }

    public function clearCache(): RedirectResponse
    {
        Artisan::call('optimize:clear');
        Artisan::call('cache:clear');
        return back()->with('status', __('custom.system_cache_cleared_successfully'));
    }

    public function runCron(RunCronRequest $request): RedirectResponse
    {
        Artisan::call($request->validated()['command_signature'], $request->get('arguments', []));
        return back()->with('status', __('custom.cron_executed'));
    }
}
