<x-guest-layout>
    <div class="space-y-4">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Sesion expirada</h1>

        <x-alert type="warning" title="Pagina expirada" :dismissible="false">
            Por seguridad, la pagina expiro o no pudimos validar tu sesion. Recarga e intenta de nuevo.
        </x-alert>

        <div class="flex flex-wrap gap-2">
            <a href="{{ url()->previous() ?: '/' }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                <x-heroicon-o-arrow-path class="w-5 h-5"/>
                <span>Intentar de nuevo</span>
            </a>
            <a href="{{ route('support.public.create') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5"/>
                <span>Soporte</span>
            </a>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5"/>
                <span>Login</span>
            </a>
        </div>
    </div>
</x-guest-layout>

