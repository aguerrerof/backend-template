<x-app-layout>
    @section('page_title', 'Proveedores logísticos · Detalle')

    @section('page_actions')
        <a
            href="{{ route('logistic-providers') }}"
            class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <x-heroicon-o-arrow-left class="h-5 w-5 text-gray-600"/>
            <span class="hidden sm:inline">Volver</span>
        </a>
        <a
            href="{{ route('logistic-providers.edit', $logisticProvider->id) }}"
            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <x-heroicon-s-pencil class="h-5 w-5"/>
            <span class="hidden sm:inline">Editar</span>
            <span class="sr-only sm:hidden">Editar</span>
        </a>
    @endsection

    <div class="space-y-6">
        <x-validation-summary />

        @if (session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        <x-card>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-inset ring-gray-200">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $logisticProvider->name }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-inset ring-gray-200">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Código</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $logisticProvider->code }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-inset ring-gray-200">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Correo de contacto</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $logisticProvider->contact_email ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-inset ring-gray-200">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Teléfono de contacto</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $logisticProvider->contact_phone ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-inset ring-gray-200 md:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">API URL</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 break-all">{{ $logisticProvider->api_url ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-inset ring-gray-200">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cancelar órdenes</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $logisticProvider->can_cancel_orders ? 'Sí' : 'No' }}</dd>
                </div>
            </dl>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Credenciales (JSON)</div>
                    <pre class="mt-2 max-h-64 overflow-auto rounded-lg bg-gray-900/90 p-4 text-xs text-gray-100">{{ json_encode($logisticProvider->credentials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Configuración (JSON)</div>
                    <pre class="mt-2 max-h-64 overflow-auto rounded-lg bg-gray-900/90 p-4 text-xs text-gray-100">{{ json_encode($logisticProvider->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>

