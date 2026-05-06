<x-guest-layout>
    <div class="flex flex-col items-center justify-center min-h-[20vh] px-6 text-center bg-gray-50">
        <div class="text-8xl font-bold text-red-500 mb-6 animate-pulse">
            500
        </div>

        <h1 class="text-4xl font-extrabold text-gray-800 mb-4">
            Transacción Fallida
        </h1>

        <p class="text-gray-600 max-w-lg mb-8 leading-relaxed">
            No se pudo realizar la transacción por problemas con el token o porque la transacción ya fue procesada.
            <br>
            Por favor, verifica los detalles e intenta nuevamente. Si el problema persiste, contacta a soporte técnico.
        </p>

        <a href="{{ config('services.shop.base_url') }}"
           class="inline-flex items-center gap-2 bg-red-500 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:bg-red-600 hover:shadow-xl transition-all duration-200 ease-in-out">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Volver al inicio
        </a>
    </div>
</x-guest-layout>
