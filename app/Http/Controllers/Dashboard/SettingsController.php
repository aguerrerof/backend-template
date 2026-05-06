<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request): view
    {
        $perPage = (int)$request->query('perPage', 5);
        $builder = Setting::query()
            ->withTrashed()
            ->orderBy('created_at');
        if ($search = trim($request->query('q', ''))) {
            $term = "%{$search}%";
            $builder->where(function ($query) use ($term) {
                $query->orWhere('key', 'like', $term)
                ->orWhere('value', 'like', $term);
            });
        }
        $settings = $builder
            ->paginate(
                in_array($perPage, [5, 10])
                    ? $perPage
                    : 5,
            )
            ->withQueryString();
        return view('settings.list', compact('settings', 'perPage'))
            ->withErrors($errors ?? null);
    }

    public function delete(string $id): RedirectResponse
    {
        Setting::findOrFail($id)->delete();
        return redirect('/settings');
    }
    public function edit(string $id): View
    {
        $setting = Setting::query()->withTrashed()->find($id);
        return view('settings.edit', compact('setting'));
    }
    public function update(UpdateSettingRequest $request, string $id): RedirectResponse
    {
        $setting = Setting::query()->withTrashed()->findOrFail($id);
        $setting->update($request->validated());
        if ($setting->deleted_at) {
            $setting->restore();
        }
        return redirect('/settings?q=' . $setting->key);
    }
}
