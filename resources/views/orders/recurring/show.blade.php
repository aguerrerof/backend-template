<x-app-layout>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center space-x-2">
                <a href="{{ route('recurring-orders.index', request()->query()) }}"
                   class="flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    <x-heroicon-s-arrow-left class="w-4 h-4 mr-2"/>
                    {{ __('Regresar') }}
                </a>
            </div>
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Cliente</label>
                    <a href="{{ $recurringOrder->userUrl }}" target="_blank"
                       class="text-green-500 hover:text-green-900 flex items-center space-x-1">
                        <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4"/>
                        <span>Ver en Shopify</span>
                    </a>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Frecuencia</label>
                    <input type="text"
                           value="{{ $recurringOrder->frequency?->name ?? '' }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           readonly>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Próxima fecha de cobro</label>
                    <input type="text"
                           value="{{ $recurringOrder->next_charge_date ?? '' }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           readonly>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Fecha de creación</label>
                    <input type="text"
                           value="{{ $recurringOrder->created_at ?? '' }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           readonly>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-1">Notas</label>
                <textarea readonly rows="3"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ $recurringOrder->notes }}</textarea>
            </div>

            @if($recurringOrder->line_items)
                <div x-data="{
                                open: false,
                                imgSrc: '',
                                show(src) { this.imgSrc = src; this.open = true; document.body.classList.add('overflow-hidden'); },
                                close() { this.open = false; document.body.classList.remove('overflow-hidden'); }
                            }"
                     class="relative mb-6 mt-6"
                >
                    <h2 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Productos</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach($recurringOrder->line_items as $value)
                            @if(isset($value->imageUrl))
                                <div
                                    class="flex flex-col items-center border p-4 rounded-lg bg-white hover:scale-105 hover:shadow-lg transition-transform cursor-pointer"
                                    @click="show('{{ $value->imageUrl }}')"
                                >
                                    <img src="{{ $value->imageUrl }}"
                                         class="w-20 h-20 object-cover rounded-lg mb-2">
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div x-show="open" x-transition
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
                         @click="close()" @keydown.escape.window="close()" style="display: none;">
                        <div class="relative" @click.stop>
                            <img :src="imgSrc"
                                 class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-2xl object-contain">
                            <button @click="close()"
                                    class="absolute -top-10 right-0 bg-white/80 text-gray-900 rounded-full p-2 hover:bg-white shadow-md">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                Ordenes asignadas
            </div>
            <div class="bg-white shadow-sm rounded-lg overflow-hidden mt-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Shopify id
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Cliente
                            </th>
                            <th colspan="3" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                Acciones
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="{{ config('services.shopify.store_url').'/admin/orders/'.$order->shopify_order_id }}" target="_blank"
                                       class="text-green-500 hover:text-green-900 flex items-center space-x-1">
                                        <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4"/>
                                        <span>{{ $order->shopify_order_id }}</span>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->order->customer->first_name ?? '' }} {{ $order->order->customer->last_name ?? '' }}</td>
                                <td class="px-3 py-4 text-sm text-center">
                                    <a href="{{ route('orders.show', array_merge(['id' => $order->id], request()->query())) }}"
                                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700">
                                        <x-heroicon-s-magnifying-glass class="w-4 h-4"/>Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No se encontraron registros
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="mt-4 dark:text-white">
                    {{ $orders->links('pagination::tailwind') }}
                </div>
            </div>
        </div>

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
