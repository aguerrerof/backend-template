@php
    $orderName = data_get($order, 'order.name') ?? ('#' . $order->id);
    $shopifyOrderId = data_get($order, 'order.id');

    $customerFirstName = data_get($order, 'order.customer.first_name');
    $customerLastName = data_get($order, 'order.customer.last_name');
    $customerName = trim(collect([$customerFirstName, $customerLastName])->filter()->implode(' '));
    $customerEmail = data_get($order, 'order.customer.email');
    $customerPhone = data_get($order, 'order.customer.phone');

    $shippingAddress = data_get($order, 'order.shipping_address');
    $shippingCity = data_get($shippingAddress, 'city') ?? $shippingCity ?? null;
    $shippingAddress1 = data_get($shippingAddress, 'address1');
    $shippingAddress2 = data_get($shippingAddress, 'address2');
    $shippingZip = data_get($shippingAddress, 'zip');
@endphp

<x-app-layout>
    @section('page_title', __('custom.create_fulfillment'))

    <div class="space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <a href="{{ route('orders.show', array_merge(['id' => $order->id], request()->query())) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition text-sm shrink-0">
                        <x-heroicon-s-arrow-left class="w-4 h-4 mr-1"/>
                        Regresar
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-lg font-semibold text-gray-900 truncate">Crear despacho</h1>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                            <span>Orden: {{ $orderName }}</span>
                            <span>ID interno: {{ $order->id }}</span>
                            @if(filled($shopifyOrderId))
                                <span>Shopify: {{ $shopifyOrderId }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="text-xs text-gray-500">
                    @if(filled($shippingCity))
                        Ciudad sugerida: <span class="font-medium text-gray-700">{{ $shippingCity }}</span>
                    @else
                        Ciudad sugerida: —
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

        @if($canSave)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-lg p-4">
                <div class="text-sm font-semibold">Hay un despacho activo para esta orden</div>
                <div class="text-sm text-yellow-800 mt-1">
                    Cancela el despacho anterior o espera a que finalice para registrar uno nuevo.
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-900 rounded-lg p-4">
                <div class="text-sm font-semibold">Listo</div>
                <div class="text-sm mt-1">{{ session('success') }}</div>
            </div>
        @endif

        <div data-fulfillment-feedback class="hidden bg-green-50 border border-green-200 text-green-900 rounded-lg p-4">
            <div class="text-sm font-semibold">Listo</div>
            <div class="text-sm mt-1" data-fulfillment-feedback-message>{{ __('custom.fulfillment_created_success') }}</div>
            <div
                data-fulfillment-provider-response
                class="mt-2 text-xs text-gray-600 hidden"
            >
                <p class="font-semibold text-[11px] text-gray-700">Respuesta del proveedor externo:</p>
                <pre class="whitespace-pre-wrap text-[11px] leading-relaxed" data-fulfillment-provider-response-body></pre>
            </div>
        </div>
        <div data-fulfillment-errors class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
            <div class="text-sm font-semibold mb-2">No se pudo guardar</div>
            <ul class="text-sm list-disc list-inside space-y-1" role="status" aria-live="polite"></ul>
        </div>

        <form
            action="{{ route('fulfillments.store') }}"
            method="POST"
            class="space-y-4"
            data-fulfillment-form
            data-order-route="{{ route('orders.show', array_merge(['id' => $order->id], request()->query())) }}"
        >
            @csrf

            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-sm font-semibold text-gray-900">Datos del despacho</h2>
                            <span class="text-xs text-gray-500">Campos obligatorios marcados con *</span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div x-data="autocomplete(@js($shippingCity))" class="relative">
                                <label for="provider" class="block text-gray-700 font-medium text-sm mb-1">
                                    Proveedor logístico <span class="text-red-600">*</span>
                                </label>

                                <div class="relative">
                                    <input
                                        id="provider"
                                        type="text"
                                        required
                                        x-model="query"
                                        @input.debounce.300ms="search()"
                                        @focus="open = true"
                                        @click.outside="open = false"
                                        placeholder="Buscar proveedor…"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pr-10 text-sm"
                                        autocomplete="off"
                                    />
                                    <button type="button"
                                            x-show="selectedId"
                                            @click="clear()"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            title="Eliminar proveedor">
                                        ×
                                    </button>
                                </div>

                                <p class="mt-1 text-xs text-gray-500">
                                    Se sugiere automáticamente por ciudad cuando es posible.
                                </p>


                                <p class="mt-1 text-xs text-gray-500" x-text="limitText"></p>

                                <ul x-show="open && filteredOptions.length > 0"
                                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-1 max-h-52 overflow-auto shadow-lg">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                        <li @click="selectOption(option)"
                                            class="px-4 py-2 cursor-pointer text-sm hover:bg-indigo-600 hover:text-white">
                                            <span x-text="option.name"></span>
                                        </li>
                                    </template>
                                </ul>

                                <input type="hidden" name="logistic_provider_id" :value="selectedId" required>
                            </div>

                            <div>
                                <label for="tracking_number" class="block text-gray-700 font-medium text-sm mb-1">
                                    Número de tracking
                                </label>
                                <input
                                    id="tracking_number"
                                    type="text"
                                    name="tracking_number"
                                    value="{{ old('tracking_number') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    placeholder="Opcional (si aplica)"
                                >
                                <p class="mt-1 text-xs text-gray-500">
                                    Si el proveedor asigna guía automáticamente, puedes dejarlo vacío.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($lineItems)
                        <x-line-items-select
                            :lineItems="$lineItems"
                            :productImages="$productImages"
                            :title="'Productos a despachar'"
                        />
                    @endif

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-sm text-gray-600">
                                Verifica el proveedor y los pesos antes de guardar.
                            </div>
                            <button
                                type="submit"
                                data-fulfillment-submit
                                @disabled($canSave)
                                @class([
                                    'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition',
                                    'bg-indigo-600 text-white hover:bg-indigo-700' => !$canSave,
                                    'bg-gray-200 text-gray-500 cursor-not-allowed' => $canSave,
                                ])
                            >
                                <x-heroicon-s-check class="w-5 h-5" aria-hidden="true"/>
                                <span data-fulfillment-submit-label>Guardar despacho</span>
                            </button>
                        </div>
                    </div>
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

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Dirección de envío</h2>
                        @if($shippingAddress)
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-[11px] font-medium text-gray-600">Dirección</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $shippingAddress1 ?? '—' }}
                                        @if(filled($shippingAddress2))
                                            <span class="text-gray-500">({{ $shippingAddress2 }})</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <dt class="text-[11px] font-medium text-gray-600">Ciudad</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $shippingCity ?? '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-medium text-gray-600">ZIP</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $shippingZip ?? '—' }}</dd>
                                    </div>
                                </div>
                            </dl>
                        @else
                            <div class="text-sm text-gray-500">No hay dirección de envío registrada.</div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function autocomplete(shippingCity = null) {
            return {
                city: shippingCity,
                query: '',
                selectedId: '',
                selectedLimit: null,
                limitText: 'Sin límite de carga definido.',
                open: false,
                filteredOptions: [],
                async init() {
                    if (!this.city) return;
                    try {
                        const res = await fetch(`/logistic-providers/autocomplete?q=&city=${this.city}`);
                        const data = await res.json();
                        if (Array.isArray(data) && data.length > 0) {
                            this.query = data[0].name;
                            this.selectedId = data[0].id;
                            this.filteredOptions = data;
                            this.updateLimitText(data[0].max_total_weight_grams ?? null);
                        }
                    } catch (error) {
                        console.error(error);
                    }
                },
                async search() {
                    if (this.query.length < 1) {
                        this.filteredOptions = [];
                        return;
                    }
                    try {
                        const res = await fetch(`/logistic-providers/autocomplete?q=${encodeURIComponent(this.query)}`);
                        const data = await res.json();
                        this.filteredOptions = Array.isArray(data) ? data : [];
                        this.open = true;
                    } catch (error) {
                        console.error(error);
                    }
                },
                selectOption(option) {
                    this.query = option.name;
                    this.selectedId = option.id;
                    this.updateLimitText(option.max_total_weight_grams ?? null);
                    this.open = false;
                },
                clear() {
                    this.query = '';
                    this.selectedId = '';
                    this.filteredOptions = [];
                    this.updateLimitText(null);
                },
                updateLimitText(limit) {
                    this.selectedLimit = limit ?? null;
                    this.limitText = this.getLimitText(limit);
                },
                getLimitText(limit) {
                    const value = Number(limit);
                    if (!Number.isFinite(value) || value <= 0) {
                        return 'Sin límite de carga definido.';
                    }
                    const kg = value / 1000;
                    const formatted = Number.isInteger(kg)
                        ? kg.toFixed(0)
                        : kg.toFixed(2).replace(/\.0+$/, '').replace(/\.$/, '');
                    return `Límite de carga: ${formatted} kg`;
                },
            }
        }
    </script>
    <script>
        (function () {
            const form = document.querySelector('[data-fulfillment-form]');
            if (!form) {
                return;
            }

            const submitButton = form.querySelector('[data-fulfillment-submit]');
            const submitLabel = submitButton?.querySelector('[data-fulfillment-submit-label]');
            const feedback = document.querySelector('[data-fulfillment-feedback]');
            const feedbackMessage = feedback?.querySelector('[data-fulfillment-feedback-message]');
            const providerResponseContainer = document.querySelector('[data-fulfillment-provider-response]');
            const providerResponseBody = providerResponseContainer?.querySelector('[data-fulfillment-provider-response-body]');
            const errorsContainer = document.querySelector('[data-fulfillment-errors]');
            const errorsList = errorsContainer?.querySelector('ul');
            const orderRoute = form.dataset.orderRoute;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const confirmMessage = @json('¿Estás seguro de proceder con esta acción?');
            const successDefault = @json(__('custom.fulfillment_created_success'));
            const fallbackError = @json('No se pudo crear el despacho. Inténtalo nuevamente.');
            const submittedLabel = submitLabel?.textContent ?? '';
            const wasInitiallyDisabled = submitButton?.hasAttribute('disabled') ?? false;

            const setLoading = () => {
                if (!submitButton) {
                    return;
                }
                submitButton.disabled = true;
                submitButton.classList.add('opacity-70', 'cursor-not-allowed');
                if (submitLabel) {
                    submitLabel.textContent = 'Guardando...';
                }
            };

            const resetLoading = () => {
                if (!submitButton) {
                    return;
                }
                if (!wasInitiallyDisabled) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
                }
                if (submitLabel) {
                    submitLabel.textContent = submittedLabel || 'Guardar despacho';
                }
            };

            const hideMessages = () => {
                if (feedback) {
                    feedback.classList.add('hidden');
                }
                if (errorsContainer) {
                    errorsContainer.classList.add('hidden');
                    if (errorsList) {
                        errorsList.innerHTML = '';
                    }
                }
                if (providerResponseContainer) {
                    providerResponseContainer.classList.add('hidden');
                    if (providerResponseBody) {
                        providerResponseBody.textContent = '';
                    }
                }
            };

            const showErrors = (messages) => {
                if (!errorsContainer || !errorsList) {
                    return;
                }
                errorsList.innerHTML = '';
                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    errorsList.appendChild(item);
                });
                errorsContainer.classList.remove('hidden');
                if (feedback) {
                    feedback.classList.add('hidden');
                }
            };

            const showSuccess = (message, providerPayload = null) => {
                if (!feedback) {
                    return;
                }
                if (feedbackMessage) {
                    feedbackMessage.textContent = message;
                }
                feedback.classList.remove('hidden');
                if (errorsContainer) {
                    errorsContainer.classList.add('hidden');
                }
                if (providerResponseContainer && providerResponseBody) {
                    const formatted = formatProviderResponse(providerPayload);
                    if (formatted) {
                        providerResponseBody.textContent = formatted;
                        providerResponseContainer.classList.remove('hidden');
                    } else {
                        providerResponseContainer.classList.add('hidden');
                    }
                }
            };

            const formatProviderResponse = (payload) => {
                if (payload === null || payload === undefined) {
                    return '';
                }
                if (typeof payload === 'string') {
                    return payload;
                }
                try {
                    return JSON.stringify(payload, null, 2);
                } catch {
                    return String(payload);
                }
            };

            const flattenErrors = (errors) => {
                if (Array.isArray(errors)) {
                    return errors;
                }
                if (typeof errors === 'object' && errors !== null) {
                    return Object.values(errors).flatMap((value) => (Array.isArray(value) ? value : [value]));
                }
                if (typeof errors === 'string') {
                    return [errors];
                }
                return [];
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!submitButton || submitButton.disabled) {
                    return;
                }
                if (!confirm(confirmMessage)) {
                    return;
                }
                hideMessages();
                setLoading();

                const formData = new FormData(form);
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                        body: formData,
                    });
                    const body = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const errors = flattenErrors(body.errors ?? body.data);
                        const messageErrors = errors.length > 0
                            ? errors
                            : body.message
                                ? [body.message]
                                : [fallbackError];
                        const providerError = body.provider_error ?? body.provider_response;
                        if (providerError && typeof providerError === 'string' && providerError.trim() !== '') {
                            messageErrors.push(`Respuesta del proveedor: ${providerError}`);
                        }
                        showErrors(messageErrors);
                        resetLoading();
                        return;
                    }
                    const successMessage = body.message ?? successDefault;
                    showSuccess(successMessage, body.provider_response);
                    window.setTimeout(() => {
                        window.location.href = body.order_route || orderRoute;
                    }, 800);
                } catch (error) {
                    const message = error?.message || fallbackError;
                    showErrors([message]);
                    resetLoading();
                }
            });
        })();
    </script>
</x-app-layout>


