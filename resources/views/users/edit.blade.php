<x-app-layout>
    @section('page_title', __('custom.edit_user'))

    @section('page_actions')
        <a
            href="{{ route('users') }}"
            class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <x-heroicon-o-arrow-left class="h-5 w-5 text-gray-600"/>
            <span class="hidden sm:inline">Volver</span>
        </a>
    @endsection

    <div class="space-y-6">
        <x-validation-summary />

        <x-card>
            <form method="POST" action="{{ route('users.update',['id'=>$user->id]) }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="name" :value="__('custom.name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('custom.password')" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <p class="mt-1 text-xs text-gray-500">Deja este campo vacío para mantener la contraseña actual.</p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('custom.password_confirmation')" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_admin" value="0">
                    <input
                        type="checkbox"
                        name="is_admin"
                        id="is_admin"
                        value="1"
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        {{ old('is_admin', (bool)($user->is_admin ?? false)) ? 'checked' : '' }}
                    >
                    <label for="is_admin" class="text-sm font-semibold text-gray-700">
                        {{ __('custom.is_admin') }}
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-indigo-500">
                        {{ __('custom.save') }}
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>

