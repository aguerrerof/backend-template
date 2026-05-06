<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogisticProviderRequest;
use App\Http\Requests\UpdateLogisticProviderRequest;
use App\Models\CityLogisticProviderRule;
use App\Models\LogisticProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogisticProvidersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $perPage = (int)$request->query('perPage', 5);
        $providers = LogisticProvider::query()
            ->withTrashed()
            ->orderBy('created_at', 'asc')
            ->paginate(
                in_array($perPage, [5, 10, 25, 50])
                    ? $perPage
                    : 5,
            )
            ->withQueryString();
        return view('logistic-providers.list', compact('providers', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('logistic-providers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLogisticProviderRequest $request): RedirectResponse
    {
        LogisticProvider::create($request->validatedData());

        return redirect('/logistic-providers');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $logisticProvider = LogisticProvider::query()->withTrashed()->find($id);
        return view('logistic-providers.edit', compact('logisticProvider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLogisticProviderRequest $request, string $id): RedirectResponse
    {
        $logisticProvider = LogisticProvider::query()->withTrashed()->findOrFail($id);
        $logisticProvider->update($request->validatedData());
        $logisticProvider->restore();
        return redirect('/logistic-providers');
    }

    public function delete(string $id): RedirectResponse
    {
        LogisticProvider::findOrFail($id)->delete();
        return redirect('/logistic-providers');
    }

    public function show(string $id): View
    {
        $logisticProvider = LogisticProvider::query()->withTrashed()->findOrFail($id);
        return view('logistic-providers.show', compact('logisticProvider'));
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $city = strtolower($request->get('city', ''));

        $providerQuery = LogisticProvider::query()
            ->where('name', 'ILIKE', "%{$query}%");

        if ($city) {
            $cityProviderIds = CityLogisticProviderRule::query()
                ->whereRaw('LOWER(city) = ?', [$city])
                ->pluck('logistic_provider_id');

            if ($cityProviderIds->isNotEmpty()) {
                $providerQuery->whereIn('id', $cityProviderIds);
            } else {
                $defaultProviderId = CityLogisticProviderRule::preloadDefaults();

                if ($defaultProviderId) {
                    $providerQuery->where('id', $defaultProviderId);
                }
            }
        }

        $providers = $providerQuery
            ->limit(10)
            ->get(['id', 'name', 'max_total_weight_grams']);

        return response()->json($providers);
    }

}
