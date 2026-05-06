<x-app-layout>
    @section('page_title', __('custom.app_mobile_settings'))

    <div class="space-y-6">
    <x-validation-summary />

    <x-card padding="p-4">
    <form method="GET" x-data="dateSearchForm()" class="flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 flex-wrap">
            <div class="relative flex-1 min-w-[250px]">
                <input type="text" name="q" x-model="q" placeholder="Buscar por indice o tipo"
                       class="w-full border rounded-md px-10 py-2 text-md focus:ring-indigo-500 focus:border-indigo-500"/>
                <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 absolute left-3 top-2.5"/>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">{{ __('custom.per-page') }}</label>
                @if(isset($perPage))
                    <select name="perPage" onchange="this.form.submit()"
                            class="border rounded-md px-2 py-1 text-md">
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    </select>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-indigo-500">
                <x-heroicon-s-magnifying-glass class="w-5 h-5"/>
                <span class="sr-only">Buscar</span>
            </x-primary-button>

            <x-secondary-button @click="clearForm()">
                <x-heroicon-s-trash class="w-5 h-5"/>
                <span class="sr-only">Limpiar</span>
            </x-secondary-button>
        </div>
    </form>
    </x-card>

    <x-card padding="p-0" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Indice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Registrado</th>
                    <th scope="col" colspan="3"
                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($settings->items() as $setting)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $setting->key }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $setting->value }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $setting->type }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 text-center">{{ $setting->created_at }}</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-center">
                            <a href="{{ route('settings.edit', $setting) }}"
                               class="inline-flex items-center justify-center rounded-md p-2 text-amber-700 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                               aria-label="Editar"
                            >
                                <x-heroicon-s-pencil class="w-5 h-5 inline-block"/>
                            </a>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-center">
                            @if(is_null($setting->deleted_at))
                                <form action="{{ route('settings.delete', $setting->id) }}" method="POST"
                                      onsubmit="return confirm('¿Estás seguro de dar de baja esta configuración?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center rounded-md p-2 text-red-700 hover:bg-red-50 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-label="Eliminar">
                                        <x-heroicon-s-trash class="w-5 h-5 inline-block"/>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0">
                            <x-empty-state title="Sin configuraciones" description="No se encontraron registros con los filtros actuales." />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if(isset($settings))
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-sm text-gray-600">
                Mostrando <span class="font-medium">{{ $settings->firstItem() ?: 0 }}</span> a
                <span class="font-medium">{{ $settings->lastItem() ?: 0 }}</span> de
                <span class="font-medium">{{ $settings->total() }}</span> resultados
            </div>
            <div class="mt-4 dark:text-white">
                {{ $settings->links('pagination::tailwind') }}
            </div>
        </div>
    @endif
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dateSearchForm', () => ({
                q: '{{ request('q') }}',

                clearForm() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('q');
                    url.searchParams.delete('from');
                    url.searchParams.delete('to');
                    url.searchParams.delete('perPage');
                    window.location.href = url.toString();
                }
            }));
        });
    </script>
</x-app-layout>
