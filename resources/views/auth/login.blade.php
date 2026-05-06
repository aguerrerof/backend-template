<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('error'))
        <x-alert type="error" class="mb-4">
            {{ session('error') }}
        </x-alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="mt-2"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                class="mt-2"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                <input
                    id="remember_me"
                    name="remember"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    checked
                >
                <span>{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a
                    class="text-sm font-medium text-indigo-700 hover:text-indigo-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    href="{{ route('password.request') }}"
                >
                    {{ __('custom.forgot-your-password') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('custom.login') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
