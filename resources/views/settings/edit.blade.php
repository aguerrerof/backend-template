<x-app-layout>
    @section('page_title', __('custom.app_mobile_settings') . ' · Editar')

    @section('page_actions')
        <a
            href="{{ url()->previous() }}"
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
            <form method="POST" action="{{ route('settings.update', $setting->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="key" value="Índice" />
                        <x-text-input id="key" name="key" type="text" class="mt-1 block w-full" :value="old('key', $setting->key)" readonly />
                        <x-input-error :messages="$errors->get('key')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="value" value="Valor" />
                        <x-text-input id="value" name="value" type="text" class="mt-1 block w-full" :value="old('value', $setting->value)" />
                        <x-input-error :messages="$errors->get('value')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" value="Tipo" />
                        <x-text-input id="type" name="type" type="text" class="mt-1 block w-full" :value="old('type', $setting->type)" />
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <a
                        href="{{ url()->previous() }}"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Cancelar
                    </a>
                    <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-indigo-500">
                        Actualizar
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>

