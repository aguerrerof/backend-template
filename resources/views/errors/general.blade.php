<x-app-layout>
    @section('page_title', 'Error inesperado')

    <div class="min-h-[60vh] flex items-center justify-center">
        <x-card class="text-center max-w-2xl w-full space-y-6">
            <div class="space-y-2">
                <p class="text-sm font-semibold uppercase tracking-wide text-red-600">Error interno</p>
                <h1 class="text-2xl font-semibold text-gray-900">Algo salió mal</h1>
                <p class="text-sm text-gray-600 leading-relaxed">
                    {{ $message ?? 'Estamos trabajando para restaurar el servicio. Puedes volver y continuar desde la pantalla anterior.' }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                <x-primary-button type="button" class="w-full sm:w-auto" onclick="history.back()">
                    Volver
                </x-primary-button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition w-full sm:w-auto">
                    Ir al inicio
                </a>
            </div>
        </x-card>
    </div>
</x-app-layout>
