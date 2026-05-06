@php
    use App\Models\Enums\FulfillmentStatus;

    $isCancelled = $fulfillment->status === FulfillmentStatus::CANCELLED->value;
    $isDelivered = $fulfillment->status === FulfillmentStatus::DELIVERED->value;
    $isImmutable = $isCancelled || $isDelivered;

    $deliveryNoteUrl = route('fulfillments.delivery-note', $fulfillment->id);
    $hasTrackingUrl = filled($fulfillment->tracking_url);
    $providerCanCancelOrders = (bool) ($fulfillment->logisticProvider?->can_cancel_orders ?? false);
    $canCancel = !$isImmutable && ($providerCanCancelOrders || !$hasTrackingUrl);

    $statusLabel = __('fulfillment-status.'.$fulfillment->status);
    $statusClasses = match ($fulfillment->status) {
        FulfillmentStatus::DELIVERED->value => 'bg-lime-50 text-lime-800 border-lime-200',
        FulfillmentStatus::CANCELLED->value => 'bg-red-50 text-red-800 border-red-200',
        FulfillmentStatus::IN_TRANSIT->value => 'bg-blue-50 text-blue-800 border-blue-200',
        FulfillmentStatus::OUT_FOR_DELIVERY->value => 'bg-teal-50 text-teal-800 border-teal-200',
        FulfillmentStatus::ISSUE->value => 'bg-pink-50 text-pink-800 border-pink-200',
        default => 'bg-gray-50 text-gray-800 border-gray-200',
    };
@endphp

<x-app-layout>
    @section('page_title', __('custom.fulfillment'))

    <div class="space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <a href="{{ route('orders.show', array_merge(['id' => $fulfillment->order->id], request()->query())) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition text-sm shrink-0">
                        <x-heroicon-s-arrow-left class="w-4 h-4 mr-1"/>
                        Regresar
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-lg font-semibold text-gray-900 truncate">
                            Despacho #{{ $fulfillment->id }}
                        </h1>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                            @if(filled($fulfillment->tracking_number))
                                <span class="text-xs text-gray-500">
                                    Guía: <span class="font-medium text-gray-700">{{ $fulfillment->tracking_number }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if(!$isCancelled)
                        <a href="{{ $deliveryNoteUrl }}" target="_blank"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition">
                            <x-heroicon-s-printer class="w-4 h-4 mr-2"/>
                            Imprimir guía
                        </a>

                        @if($hasTrackingUrl)
                            <a href="{{ $fulfillment->tracking_url }}" target="_blank"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                <x-heroicon-s-truck class="w-4 h-4 mr-2"/>
                                Ver tracking
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                <div class="text-sm font-semibold mb-2">No se pudo guardar</div>
                <ul class="text-sm list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-900 rounded-lg p-4">
                <div class="text-sm font-semibold">Listo</div>
                <div class="text-sm mt-1">{{ session('success') }}</div>
            </div>
        @endif

        @if($isImmutable)
            <div class="bg-gray-50 border border-gray-200 text-gray-800 rounded-lg p-4">
                <div class="text-sm font-semibold">Este despacho no se puede editar</div>
                <div class="text-sm text-gray-700 mt-1">
                    @if($isDelivered)
                        Está marcado como <span class="font-medium">Entregado</span>.
                    @else
                        Está marcado como <span class="font-medium">Cancelado</span>.
                    @endif
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Detalle</h2>

                    <form id="update-fulfillment-form"
                          action="{{ route('fulfillments.update', $fulfillment->id) }}"
                          method="POST"
                          class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium text-sm mb-1">Número de tracking</label>
                                <input type="text"
                                       value="{{ $fulfillment->tracking_number }}"
                                       disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>
                             <div>
                                <label class="block text-gray-700 font-medium text-sm mb-1">Número de pedido</label>
                                <input type="text"
                                       value="{{ $fulfillment->tracking_info['num_pedido'] ?? ''}}"
                                       disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium text-sm mb-1">Estado</label>
                                <input type="text"
                                       value="{{ $statusLabel }}"
                                       disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium text-sm mb-1">Proveedor logístico</label>
                            <input type="text"
                                   readonly
                                   value="{{ $fulfillment->logisticProvider?->name ?? '—' }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium text-sm mb-1">Fecha de despacho</label>
                                <input
                                    x-data
                                    x-init="flatpickr($el, { enableTime: true, dateFormat: 'Y-m-d H:i' })"
                                    name="dispatched_at"
                                    type="text"
                                    @disabled($isImmutable)
                                    value="{{ $fulfillment->dispatched_at?->format('Y-m-d H:i') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                >
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium text-sm mb-1">Fecha de entrega al cliente</label>
                                <input
                                    x-data
                                    x-init="flatpickr($el, { enableTime: true, dateFormat: 'Y-m-d H:i' })"
                                    name="delivered_at"
                                    type="text"
                                    @disabled($isImmutable)
                                    value="{{ $fulfillment->delivered_at?->format('Y-m-d H:i') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                >
                            </div>
                        </div>

                        @if(!$isImmutable)
                            <div class="pt-2 space-y-2">
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <button type="submit"
                                            data-update-button
                                            onclick="return confirm('¿Estás seguro de proceder con esta acción?');"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2 w-full bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                                    >
                                        <x-heroicon-s-arrow-path class="w-4 h-4"/>
                                        <span data-update-label>Actualizar despacho</span>
                                    </button>
                                    @if($canCancel)
                                        <button
                                            type="button"
                                            data-cancel-fulfillment-route="{{ route('fulfillments.cancel', $fulfillment->id) }}"
                                            data-order-route="{{ route('orders.show', array_merge(['id' => $fulfillment->order->id], request()->query())) }}"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2 w-full bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                        >
                                            <x-heroicon-s-x-circle class="w-4 h-4"/>
                                            Cancelar pedido o despacho
                                        </button>
                                    @endif
                                </div>
                                @if($canCancel)
                                    <div class="space-y-2">
                                        <div class="rounded-lg border border-yellow-200/80 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                                            <p class="font-semibold text-xs uppercase tracking-wide text-yellow-700">Cancelar pedido o despacho</p>
                                            <p class="mt-1">
                                                {{ __('custom.fulfillment_cancellation_instruction', ['provider' => $fulfillment->logisticProvider?->name ?? __('custom.fulfillment')]) }}
                                            </p>
                                        </div>
                                        <p
                                            data-cancel-feedback
                                            class="text-sm text-red-600 invisible"
                                            role="status"
                                            aria-live="polite"
                                        ></p>
                                    </div>
                                @endif
                            </div>
                            <script>
                                (function () {
                                    const button = document.querySelector('[data-update-button]');
                                    if (!button) {
                                        return;
                                    }
                                    const form = button.closest('form');
                                    const label = button.querySelector('[data-update-label]');

                                    const startLoading = () => {
                                        button.disabled = true;
                                        button.classList.add('opacity-70', 'cursor-not-allowed');
                                        if (label) {
                                            label.textContent = 'Actualizando...';
                                        }
                                    };

                                    if (form) {
                                        form.addEventListener('submit', startLoading);
                                    }
                                })();
                            </script>
                            @if($canCancel)
                                <script>
                                    (function () {
                                        const init = () => {
                                            const button = document.querySelector('[data-cancel-fulfillment-route]');
                                            if (!button) {
                                                return;
                                            }
                                            const feedback = document.querySelector('[data-cancel-feedback]');
                                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                            const initialContent = button.innerHTML;
                                            const confirmMessage = '¿Seguro que deseas cancelar este despacho?';
                                            const successText = @json(__('custom.fulfillment_cancelled_success'));

                                            const showFeedback = (message, isError = true) => {
                                                if (!feedback) {
                                                    return;
                                                }
                                                feedback.textContent = message;
                                                feedback.classList.remove('invisible');
                                                feedback.classList.toggle('text-red-600', isError);
                                                feedback.classList.toggle('text-lime-600', !isError);
                                            };

                                            const resetButton = () => {
                                                button.disabled = false;
                                                button.innerHTML = initialContent;
                                                button.classList.remove('cursor-not-allowed', 'opacity-70');
                                            };

                                            button.addEventListener('click', async () => {
                                                if (!confirm(confirmMessage)) {
                                                    return;
                                                }
                                                button.disabled = true;
                                                button.classList.add('opacity-70', 'cursor-not-allowed');
                                                button.textContent = 'Cancelando...';
                                                try {
                                                    const response = await fetch(button.dataset.cancelFulfillmentRoute, {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'Accept': 'application/json',
                                                            'X-CSRF-TOKEN': csrfToken || '',
                                                        },
                                                        body: JSON.stringify({}),
                                                    });
                                                    const body = await response.json().catch(() => ({}));
                                                    if (!response.ok) {
                                                        throw new Error(body.message || 'No se pudo cancelar el despacho. Inténtalo nuevamente.');
                                                    }
                                                    showFeedback(body.message || successText, false);
                                                    setTimeout(() => {
                                                        window.location.href = body.order_route || button.dataset.orderRoute;
                                                    }, 850);
                                                } catch (error) {
                                                    resetButton();
                                                    showFeedback(error?.message || 'No se pudo cancelar el despacho. Inténtalo nuevamente.');
                                                }
                                            });
                                        };

                                        if (document.readyState !== 'loading') {
                                            init();
                                        } else {
                                            document.addEventListener('DOMContentLoaded', init);
                                        }
                                    })();
                                </script>
                            @endif
                        @endif
                    </form>
                </div>
            </div>

            <div class="space-y-4">
    <div x-data="{ open: false }" class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
        <button @click="open = !open" class="w-full flex items-center justify-between p-4 focus:outline-none hover:bg-gray-50 transition">
            <h2 class="text-sm font-semibold text-gray-900">Orden</h2>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        
        <div x-show="open" x-cloak class="p-4 pt-0">
            <dl class="space-y-3">
                <div>
                    <dt class="text-[11px] font-medium text-gray-600">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('orders.show', array_merge(['id' => $fulfillment->order->id], request()->query())) }}"
                           class="text-indigo-600 hover:text-indigo-800 font-medium">
                            #{{ $fulfillment->order->id }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-600">Notas</dt>
                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
                        {{ filled($fulfillment->order?->notes) ? $fulfillment->order->notes : '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div x-data="{ open: false }" class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
        <button @click="open = !open" class="w-full flex items-center justify-between p-4 focus:outline-none hover:bg-gray-50 transition">
            <h2 class="text-sm font-semibold text-gray-900">Metadatos</h2>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" x-cloak class="p-4 pt-0">
            <dl class="space-y-3">
                <div>
                    <dt class="text-[11px] font-medium text-gray-600">Creado</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fulfillment->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-600">Última actualización</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fulfillment->updated_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-medium text-gray-600">Encargado</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fulfillment->user?->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
    <div x-data="{ open: false }" class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
        <button @click="open = !open" class="w-full flex items-center justify-between p-4 focus:outline-none hover:bg-gray-50 transition">
            <h2 class="text-sm font-semibold text-gray-900">Tracking Information</h2>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" x-cloak class="p-4 pt-0">
            <dl class="space-y-3">
                @foreach ($fulfillment->tracking_info as $key => $trackingInformation)
                    <div>
                        <dt class="text-[11px] font-medium text-gray-600">{{ $key }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{$trackingInformation }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</div>
        </div>

        @if(isset($fulfillment->tracking_info['novedades']))
            <div class="grid grid-cols-1 gap-1">
                <x-tracking-status-updates :fulfillment="$fulfillment"/>
            </div>
        @endif

        @if($fulfillment->line_items)
            <div class="grid grid-cols-1 gap-1">
                <x-line-items-list
                    :lineItems="$fulfillment->line_items"
                    :productImages="$productImages"
                    title="Listado de productos despachados o por despachar"
                />
            </div>
        @endif

        @if(isset($fulfillment->tracking_info['imagenes']))
            <div class="grid grid-cols-1 gap-1">
                <x-images-tracking
                    :images="$fulfillment->tracking_info['imagenes'] ?? []"
                    title="Listado de imágenes de novedades por parte del proveedor"
                />
            </div>
        @endif
    </div>
</x-app-layout>
