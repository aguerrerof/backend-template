<x-app-layout>
    @section('page_title', __('custom.activity_logs'))

    <div class="space-y-6">
        <x-validation-summary />

        <x-card padding="p-4">
        <form method="GET" x-data="dateSearchForm()" class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 flex-wrap">
                <div class="relative flex-1 min-w-[250px]">
                    <input type="text" name="q" x-model="q" placeholder="Buscar por nivel o mensaje"
                           class="w-full border rounded-md px-10 py-2 text-md focus:ring-indigo-500 focus:border-indigo-500"/>
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 absolute left-3 top-2.5"/>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label for="from" class="text-sm text-gray-600">Desde</label>
                        <input x-model="from" @change="validateDates()" type="date" id="from" name="from"
                               max="{{ now()->toDateString() }}"
                               class="border rounded-md px-2 py-1 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="to" class="text-sm text-gray-600">Hasta</label>
                        <input x-model="to" @change="validateDates()" type="date" id="to" name="to"
                               max="{{ now()->toDateString() }}"
                               class="border rounded-md px-2 py-1 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">{{ __('custom.per-page') }}</label>
                    @if(isset($perPage))
                        <select name="perPage" onchange="this.form.submit()"
                                class="border rounded-md px-2 py-1 text-md">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <x-primary-button class="!bg-indigo-600 hover:!bg-indigo-700 focus:!ring-indigo-500">
                    <x-heroicon-s-magnifying-glass class="w-5 h-5"/>
                    <span class="sr-only">Buscar</span>
                </x-primary-button>

                <x-secondary-button @click="clearForm()">
                    <x-heroicon-s-trash class="w-5 h-5"/>
                    <span class="sr-only">Limpiar</span>
                </x-secondary-button>
            </div>
        </form>
        </x-card>

        <x-card padding="p-0" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nivel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Mensaje</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Registrado</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($logs->items() as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $log->level }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $log->message }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 text-center">{{ $log->created_at }}</td>
                            <td class="px-3 py-4 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                                        data-context='@json($log->context)'
                                        onclick="showLogJson(this)"
                                    >
                                        <x-heroicon-s-eye class="w-4 h-4 mr-1"/>Ver JSON
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700"
                                        data-context='@json($log->context)'
                                        data-log-id="{{ $log->id }}"
                                        onclick="downloadLogJson(this)"
                                    >
                                        <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-1"/>Descargar JSON
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-0">
                                <x-empty-state title="Sin registros" description="No se encontraron actividades con los filtros actuales." />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <div id="json-modal"
             class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-3xl rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-800">Detalle JSON</h3>
                    <button type="button"
                            class="rounded px-2 py-1 text-sm text-gray-500 hover:bg-gray-100"
                            onclick="closeJsonModal()">
                        Cerrar
                    </button>
                </div>
                <pre id="json-modal-content"
                     class="max-h-[70vh] overflow-auto p-4 text-xs text-gray-800"></pre>
            </div>
        </div>

        @if(isset($logs))
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Mostrando <span class="font-medium">{{ $logs->firstItem() ?: 0 }}</span> a
                    <span class="font-medium">{{ $logs->lastItem() ?: 0 }}</span> de
                    <span class="font-medium">{{ $logs->total() }}</span> resultados
                </div>
                <div class="mt-4 dark:text-white">
                    {{ $logs->links('pagination::tailwind') }}
                </div>
            </div>
        @endif
    </div>

    <script>
        function parseLogContext(rawContext) {
            try {
                return typeof rawContext === 'string' ? JSON.parse(rawContext) : rawContext;
            } catch (error) {
                return rawContext;
            }
        }

        function showLogJson(button) {
            const context = parseLogContext(button.dataset.context ?? '{}');
            const modal = document.getElementById('json-modal');
            const content = document.getElementById('json-modal-content');

            if (!modal || !content) {
                return;
            }

            content.textContent = JSON.stringify(context, null, 2);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeJsonModal() {
            const modal = document.getElementById('json-modal');

            if (!modal) {
                return;
            }

            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        function downloadLogJson(button) {
            const context = parseLogContext(button.dataset.context ?? '{}');
            const logId = button.dataset.logId ?? 'item';
            const fileName = `activity-log-${logId}-context.json`;
            const blob = new Blob([JSON.stringify(context, null, 2)], {type: 'application/json'});
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('dateSearchForm', () => ({
                q: '{{ request('q') }}',
                from: '{{ request('from') }}',
                to: '{{ request('to') }}',
                today: new Date().toISOString().split('T')[0],

                validateDates() {
                    if (this.from && this.to) {
                        const fromDate = new Date(this.from);
                        const toDate = new Date(this.to);
                        const today = new Date(this.today);
                        if (toDate > today) {
                            alert('La fecha final no puede ser futura.');
                            this.to = this.today;
                            return;
                        }
                        if (fromDate > today) {
                            alert('La fecha inicial no puede ser futura.');
                            this.from = this.today;
                            return;
                        }
                        if (toDate < fromDate) {
                            alert('La fecha final no puede ser anterior a la inicial.');
                            this.to = '';
                            return;
                        }
                        const diffDays = (toDate - fromDate) / (1000 * 60 * 60 * 24);
                        if (diffDays > 31) {
                            alert('El rango máximo permitido es de 1 mes.');
                            this.to = '';
                        }
                    }
                },

                clearForm() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('q');
                    url.searchParams.delete('from');
                    url.searchParams.delete('to');
                    url.searchParams.delete('perPage');
                    window.location.href = url.toString();
                }
            }));
        });
    </script>
</x-app-layout>
