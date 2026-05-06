<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Soporte</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Crea un ticket y te contactaremos lo mas pronto posible.</p>
    </div>

    @if ($errors->has('support'))
        <x-alert type="warning" title="Limite alcanzado">
            {{ $errors->first('support') }}
        </x-alert>
        <div class="h-4"></div>
    @endif

    <x-alert type="info" title="Tip" :dismissible="true">
        Si el problema es con un despacho, incluye el numero de orden y el proveedor (LAAR / Urbano) para ayudarte mas rapido.
    </x-alert>

    <x-validation-summary />

    <form method="POST" action="{{ route('support.public.store') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="guest_name" value="Nombre" />
            <x-text-input id="guest_name" name="guest_name" type="text" class="mt-1 block w-full" :value="old('guest_name')" required maxlength="150" />
            <x-input-error :messages="$errors->get('guest_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="guest_email" value="Correo" />
            <x-text-input id="guest_email" name="guest_email" type="email" class="mt-1 block w-full" :value="old('guest_email')" required maxlength="190" />
            <x-input-error :messages="$errors->get('guest_email')" class="mt-2" />
        </div>

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

        <div class="flex items-center justify-end">
            <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-indigo-500">
                Enviar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
