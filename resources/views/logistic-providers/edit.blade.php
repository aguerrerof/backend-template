<x-app-layout>
    @section('page_title', 'Proveedores logísticos · Editar')

    @section('page_actions')
        <a
            href="{{ route('logistic-providers') }}"
            class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <x-heroicon-o-arrow-left class="h-5 w-5 text-gray-600"/>
            <span class="hidden sm:inline">Volver</span>
        </a>
    @endsection

    <div class="space-y-6">
        <x-validation-summary />

        @if (session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        <x-card>
            <div class="flex justify-between items-center mb-4 gap-2">
                <a
                    href="{{ route('logistic-providers') }}"
                    class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <x-heroicon-o-arrow-left class="h-5 w-5 text-gray-600"/>
                    Regresar
                </a>
            </div>
            <form method="POST" action="{{ route('logistic-providers.update', $logisticProvider) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Nombre del proveedor" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $logisticProvider->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="code" value="Código interno" />
                        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $logisticProvider->code)" required />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="contact_email" value="Correo de contacto" />
                        <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email', $logisticProvider->contact_email)" />
                        <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="contact_phone" value="Teléfono de contacto" />
                        <x-text-input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full" :value="old('contact_phone', $logisticProvider->contact_phone)" />
                        <x-input-error :messages="$errors->get('contact_phone')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="api_url" value="API URL" />
                    <x-text-input id="api_url" name="api_url" type="text" class="mt-1 block w-full" :value="old('api_url', $logisticProvider->api_url)" placeholder="https://api.proveedor.com/" />
                    <x-input-error :messages="$errors->get('api_url')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="credentials" value="Credenciales (JSON)" />
                        <textarea id="credentials" name="credentials" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder='{"api_key": "clave", "secret": "secreto"}'>{{ old('credentials', json_encode($logisticProvider->credentials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                        <x-input-error :messages="$errors->get('credentials')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="config" value="Configuración (JSON)" />
                        <textarea id="config" name="config" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder='{"sandbox": true, "supports_tracking": true}'>{{ old('config', json_encode($logisticProvider->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                        <x-input-error :messages="$errors->get('config')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="max_total_weight_kg" value="Límite de carga (kg)" />
                    <x-text-input
                        id="max_total_weight_kg"
                        name="max_total_weight_kg"
                        type="number"
                        min="0.01"
                        step="0.01"
                        class="mt-1 block w-full"
                        :value="old('max_total_weight_kg', $logisticProvider->max_total_weight_grams ? ($logisticProvider->max_total_weight_grams / 1000) : null)"
                        placeholder="Dejar en blanco si no aplica"
                    />
                    <p class="mt-1 text-xs text-gray-500">Define el peso máximo permitido por este proveedor. Déjalo vacío para indicar que no aplica límite.</p>
                    <x-input-error :messages="$errors->get('max_total_weight_kg')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="can_cancel_orders" value="0">
                    <input
                        type="checkbox"
                        id="can_cancel_orders"
                        name="can_cancel_orders"
                        value="1"
                        @checked(old('can_cancel_orders', $logisticProvider->can_cancel_orders))
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <label for="can_cancel_orders" class="text-sm font-semibold text-gray-700">
                        Permitir cancelación de órdenes
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <a
                        href="{{ route('logistic-providers') }}"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Cancelar
                    </a>
                    <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-indigo-500">
                        Actualizar proveedor
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
