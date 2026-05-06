<x-app-layout>
    @section('page_title', 'Ticket #' . $ticket->id)

    <div class="space-y-6">
        <x-validation-summary />

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Ticket #{{ $ticket->id }}</h1>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($ticket->status) }} · {{ $ticket->created_at?->format('Y-m-d H:i') }}</div>
            </div>
            <a href="{{ route('support-tickets.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <x-heroicon-o-arrow-left class="w-5 h-5"/>
                <span>Volver</span>
            </a>
        </div>

        <x-card padding="p-6">
            <div class="space-y-4">
                <div>
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Asunto</div>
                    <div class="text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Mensaje</div>
                    <div class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $ticket->message }}</div>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>

