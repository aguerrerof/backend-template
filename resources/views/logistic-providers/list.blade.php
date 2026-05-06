<x-app-layout>
    @section('page_title', __('custom.logistic-providers'))

    @section('page_actions')
        <a
            href="{{ route('logistic-providers.create') }}"
            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <x-heroicon-s-plus class="h-5 w-5"/>
            <span class="hidden sm:inline">{{ __('custom.create_logistic_provider') }}</span>
            <span class="sr-only sm:hidden">{{ __('custom.create_logistic_provider') }}</span>
        </a>

        <form method="GET" class="flex items-center gap-2">
            <label class="hidden sm:block text-sm text-gray-600">{{ __('custom.per-page') }}</label>
            <select name="perPage" onchange="this.form.submit()" class="border border-gray-300 rounded-md px-2 py-2 text-sm bg-white focus:ring-indigo-500 focus:border-indigo-500">
                <option value="5" {{ $perPage==5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ $perPage==10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $perPage==25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $perPage==50 ? 'selected' : '' }}>50</option>
            </select>
        </form>
    @endsection

    <div class="space-y-6">
    <x-card padding="p-0" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">C&oacute;digo
                </th>
                <th scope="col"
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                </th>
            </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($providers->items() as $provider)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $provider->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $provider->contact_email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $provider->code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('logistic-providers.edit', $provider->id) }}"
                               class="inline-flex items-center justify-center rounded-md p-2 text-amber-700 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                               aria-label="Editar"
                            >
                                <x-heroicon-s-pencil class="w-5 h-5 inline-block"/>
                            </a>
                            @if(is_null($provider->deleted_at))
                                <form action="{{ route('logistic-providers.delete', $provider->id) }}" method="POST"
                                      onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center rounded-md p-2 text-red-700 hover:bg-red-50 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-label="Eliminar">
                                        <x-heroicon-s-trash class="w-5 h-5 inline-block"/>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-0">
                        <x-empty-state title="Sin proveedores" description="No hay proveedores logísticos para mostrar." />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </x-card>

    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-gray-600">
            Mostrando <span class="font-medium">{{ $providers->firstItem() ?: 0 }}</span> a
            <span class="font-medium">{{ $providers->lastItem() ?: 0 }}</span> de
            <span class="font-medium">{{ $providers->total() }}</span> resultados
        </div>

        <div class="mt-4 dark:text-white">
            {{ $providers->links('pagination::tailwind') }}
        </div>
    </div>
    </div>
</x-app-layout>
