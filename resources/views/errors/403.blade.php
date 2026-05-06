<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('custom.access_denied') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8 flex flex-col items-center text-center">
                <!-- Icon -->
                <svg class="w-9 h-9 text-red-600 mb-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z" />
                </svg>
                <h1 class="text-6xl font-extrabold text-red-600 mb-4"> {{ __('custom.access_denied') }}</h1>
                <p class="text-gray-600 mb-6">{{ __('custom.access_denied_full_message') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
