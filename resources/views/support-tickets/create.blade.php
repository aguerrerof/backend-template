<x-app-layout>
    @section('page_title', 'Nuevo ticket')

    <div class="space-y-6">
        <x-validation-summary />

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Nuevo ticket de soporte</h1>
            <a href="{{ route('support-tickets.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <x-heroicon-o-arrow-left class="w-5 h-5"/>
                <span>Volver</span>
            </a>
        </div>

        <x-card padding="p-6">
            <form method="POST" action="{{ route('support-tickets.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="subject" value="Asunto" />
                    <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" :value="old('subject')" required maxlength="150" />
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="message" value="Mensaje" />
                    <textarea id="message" name="message" rows="6"
                              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                              required maxlength="5000">{{ old('message') }}</textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                </div>

                <div class="flex justify-end">
                    <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-indigo-500">
                        Enviar
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>

