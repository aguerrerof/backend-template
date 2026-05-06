<x-guest-layout>
    <div class="space-y-4">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Ticket enviado</h1>

        @if(session('ticket_id'))
            <x-alert type="success" title="Recibido" :dismissible="false">
                Recibimos tu solicitud. Tu numero de ticket es <span class="font-semibold">#{{ session('ticket_id') }}</span>.
            </x-alert>
        @else
            <x-alert type="success" title="Recibido" :dismissible="false">
                Recibimos tu solicitud. Te contactaremos pronto.
            </x-alert>
        @endif

        <a href="{{ route('support.public.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
            <x-heroicon-o-plus class="w-5 h-5"/>
            <span>Crear otro ticket</span>
        </a>
    </div>
</x-guest-layout>
