<x-app-layout>
    @section('page_title', __('custom.orders'))

    <div class="space-y-6">
        <x-validation-summary />

        <x-card padding="p-4">
            <form method="GET" x-data="dateSearchForm()" class="flex flex-col gap-4">

        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 flex-wrap">
            <div class="relative flex-1 min-w-[250px]">
                <input
                    type="text"
                    name="q"
                    x-model="q"
                    placeholder="Buscar por id de shopify,número de orden o nombre del cliente"
                    class="w-full border rounded-md px-10 py-2 text-md focus:ring-indigo-500 focus:border-indigo-500"
                />
                <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 absolute left-3 top-2.5"/>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <label for="from" class="text-sm text-gray-600">Desde</label>
                    <input
                        x-model="from"
                        @change="validateDates"
                        type="date"
                        id="from"
                        name="from"
                        max="{{ now()->toDateString() }}"
                        class="border rounded-md px-2 py-1 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <label for="to" class="text-sm text-gray-600">Hasta</label>
                    <input
                        x-model="to"
                        @change="validateDates"
                        type="date"
                        id="to"
                        name="to"
                        max="{{ now()->toDateString() }}"
                        class="border rounded-md px-2 py-1 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"># de Orden</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Registrado en
                        Shopify
                    </th>
                    <th colspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Acciones
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @if(isset($orders))
                    @forelse ($orders->items() as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $order->order->order_number ?? null }}</td>

                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $order->order->customer->first_name ?? '' }} {{ $order->order->customer->last_name ?? '' }}</td>
                            <td class="px-2 py-4 text-sm text-center text-gray-500 dark:text-gray-300">
                                {{ $order->order->financial_status
                                    ?__('order-status.'.$order->order->financial_status)
                                    : null
                                    }}
                            </td>
                            <td class="px-2 py-4 text-sm text-center text-gray-500 dark:text-gray-300">{{ $order->created_at_shopify }}</td>
                            <td class="px-2 py-4 text-sm text-center">
                                <a href="{{ route('orders.show', array_merge(['id' => $order->id], request()->query())) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <x-heroicon-s-magnifying-glass class="w-4 h-4 text-gray-600 dark:text-gray-300"/>
                                    <span>Ver</span>
                                </a>
                                <a href="{{ config('services.shopify.store_url').'/admin/orders/'.$order->shopify_order_id }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                                    <x-heroicon-s-shopping-bag class="w-4 h-4"/>
                                    <span>Shopify</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-0">
                                <x-empty-state title="Sin resultados" description="No se encontraron órdenes con los filtros actuales." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @endif
            </table>
        </div>
        </x-card>

    {{-- Paginación --}}
    @if(isset($orders))
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-sm text-gray-600">
                Mostrando <span class="font-medium">{{ $orders->firstItem() ?: 0 }}</span> a
                <span class="font-medium">{{ $orders->lastItem() ?: 0 }}</span> de
                <span class="font-medium">{{ $orders->total() }}</span> resultados
            </div>

            <div class="mt-4 dark:text-white">
                {{ $orders->links('pagination::tailwind') }}
            </div>
        </div>
    @endif
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dateSearchForm', () => ({
                q: '{{ request('q') }}',
                from: '{{ request('from') }}',
                to: '{{ request('to') }}',
                today: new Date().toISOString().split('T')[0],

                validateDates() {
                    if (this.from && this.to) {
                        const fromDate = new Date(this.from);
                        const toDate = new Date(this.to);
                        const today = new Date(this.today);

                        if (toDate > today) {
                            alert('La fecha final no puede ser futura.');
                            this.to = this.today;
                            return;
                        }
                        if (fromDate > today) {
                            alert('La fecha inicial no puede ser futura.');
                            this.from = this.today;
                            return;
                        }
                        if (toDate < fromDate) {
                            alert('La fecha final no puede ser anterior a la inicial.');
                            this.to = '';
                            return;
                        }

                        const diffDays = (toDate - fromDate) / (1000 * 60 * 60 * 24);
                        if (diffDays > 31) {
                            alert('El rango máximo permitido es de 1 mes.');
                            this.to = '';
                        }
                    }
                },

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
