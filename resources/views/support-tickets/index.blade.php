<x-app-layout>
    @section('page_title', 'Soporte')

    <div class="space-y-6">
        <x-validation-summary />

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Tickets de soporte</h1>
            <a href="{{ route('support-tickets.create') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                <x-heroicon-o-plus class="w-5 h-5"/>
                <span>Nuevo ticket</span>
            </a>
        </div>

        <x-card padding="p-0" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Asunto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($tickets->items() as $ticket)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</td>
                            <td class="px-6 py-4 text-sm text-center text-gray-600 dark:text-gray-300">
                                {{ ucfirst($ticket->status) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-600 dark:text-gray-300">{{ $ticket->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-center">
                                <a href="{{ route('support-tickets.show', ['ticket' => $ticket->id]) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <x-heroicon-s-magnifying-glass class="w-4 h-4 text-gray-600 dark:text-gray-300"/>
                                    <span>Ver</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-0">
                                <x-empty-state title="Sin tickets" description="Aun no tienes tickets de soporte." />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-4 dark:text-white">
            {{ $tickets->links('pagination::tailwind') }}
        </div>
    </div>
</x-app-layout>

