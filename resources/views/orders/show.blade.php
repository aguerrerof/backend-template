<x-app-layout>
    @section('page_title', __('custom.order'))
    @php
        $orderName = data_get($order, 'order.name') ?? ('#' . $order->id);
        $shopifyOrderId = data_get($order, 'order.id');

        $customerFirstName = data_get($order, 'order.customer.first_name');
        $customerLastName = data_get($order, 'order.customer.last_name');
        $customerName = trim(collect([$customerFirstName, $customerLastName])->filter()->implode(' '));
        $customerEmail = data_get($order, 'order.customer.email');
        $customerPhone = data_get($order, 'order.customer.phone');

        $notes = trim((string) ($order->notes ?? ''));
        $shippingAddress = data_get($order, 'order.shipping_address');
    @endphp

    <div class="space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <a href="{{ route('orders', request()->query()) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition text-sm shrink-0">
                        <x-heroicon-s-arrow-left class="w-4 h-4 mr-1"/>
                        Regresar
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-lg font-semibold text-gray-900 truncate">Orden {{ $orderName }}</h1>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                            <span>ID interno: {{ $order->id }}</span>
                            @if(filled($shopifyOrderId))
                                <span>Shopify: {{ $shopifyOrderId }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-list-fulfillments
            :fulfillments="$fulfillments"
            :order="$order"
        />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Notas</h2>
                        @if(filled($notes))
                            <span class="text-xs text-gray-500">{{ mb_strlen($notes) }} caracteres</span>
                        @endif
                    </div>
                    @if(filled($notes))
                        <div class="text-sm text-gray-800 whitespace-pre-wrap">{{ $notes }}</div>
                    @else
                        <div class="text-sm text-gray-500">Sin notas registradas.</div>
                    @endif
                </div>

                @if($shippingAddress)
                    <div x-data="{ open: true }" class="bg-white shadow-sm sm:rounded-lg p-4">
                        <button type="button"
                                @click="open = !open"
                                class="flex items-center justify-between w-full text-left">
                            <span class="text-sm font-semibold text-gray-900">Datos de envío</span>
                            <span class="inline-flex items-center gap-2 text-xs text-gray-500">
                                <span x-show="!open">Mostrar</span>
                                <span x-show="open">Ocultar</span>
                                <span>
                                    <template x-if="!open">
                                        <x-heroicon-s-chevron-down class="w-5 h-5 text-gray-500"/>
                                    </template>
                                    <template x-if="open">
                                        <x-heroicon-s-chevron-up class="w-5 h-5 text-gray-500"/>
                                    </template>
                                </span>
                            </span>
                        </button>

                        <div x-show="open" x-transition class="mt-4">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($shippingAddress as $key => $value)
                                    <div class="rounded-md border border-gray-100 p-3 bg-gray-50">
                                        <dt class="text-[11px] font-medium text-gray-600">
                                            {{ __('shipping-address.'.$key) }}
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            @if(is_array($value) || is_object($value))
                                                <pre class="text-xs text-gray-800 whitespace-pre-wrap">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @else
                                                {{ filled($value) ? $value : '—' }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Cliente</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-[11px] font-medium text-gray-600">Nombre</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ filled($customerName) ? $customerName : '—' }}</dd>
                        </div>
                        @if(filled($customerEmail))
                            <div>
                                <dt class="text-[11px] font-medium text-gray-600">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 break-words">
                                    <a href="mailto:{{ $customerEmail }}" class="text-indigo-600 hover:text-indigo-800">{{ $customerEmail }}</a>
                                </dd>
                            </div>
                        @endif
                        @if(filled($customerPhone))
                            <div>
                                <dt class="text-[11px] font-medium text-gray-600">Teléfono</dt>
                                <dd class="mt-1 text-sm text-gray-900 break-words">{{ $customerPhone }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
