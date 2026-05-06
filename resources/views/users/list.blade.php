<x-app-layout>
    @section('page_title', __('custom.users'))

    @section('page_actions')
        <a
            href="{{ route('users.create') }}"
            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <x-heroicon-s-plus class="h-5 w-5"/>
            <span class="hidden sm:inline">{{ __('custom.create_user') }}</span>
            <span class="sr-only sm:hidden">{{ __('custom.create_user') }}</span>
        </a>

        <form method="GET" class="flex items-center gap-2">
            <label class="hidden sm:block text-sm text-gray-600">{{ __('custom.per-page') }}</label>
            <select name="perPage" onchange="this.form.submit()" class="border border-gray-300 dark:border-gray-700 rounded-md px-2 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="5" {{ $perPage==5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ $perPage==10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $perPage==25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $perPage==50 ? 'selected' : '' }}>50</option>
            </select>
        </form>
    @endsection

    <div class="space-y-6">

    <x-card padding="p-0" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Creado
                </th>
                <th scope="col"
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{__('custom.is_admin')}}
                </th>
                <th scope="col" colspan="2"
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Acciones
                </th>
            </tr>
            </thead>

            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($users->items() as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">
                        {{ (bool)$user->is_admin === true? 'Si':'No' }}
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-center">
                        <!-- Edit Icon -->
                        @if(is_null($user->deleted_at))
                            <a href="{{ route('users.edit', $user) }}"
                               class="inline-flex items-center justify-center rounded-md p-2 text-amber-700 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                               aria-label="Editar"
                            >
                                <x-heroicon-s-pencil class="w-5 h-5 inline-block"/>
                            </a>
                        @endif
                    </td>

                    <td class="px-3 py-4 whitespace-nowrap text-sm text-center">
                        @if($user->deleted_at)
                            <a href="{{ route('users.activate', $user) }}"
                               class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                <x-heroicon-s-check-circle class="w-5 h-5"/>
                                <span>Activar</span>
                            </a>
                        @else
                            <a href="{{ route('users.deactivate', $user) }}"
                               class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                <x-heroicon-s-x-circle class="w-5 h-5"/>
                                <span>Desactivar</span>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-0">
                        <x-empty-state title="Sin usuarios" description="No hay usuarios para mostrar." />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </x-card>

    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-gray-600">
            Mostrando <span class="font-medium">{{ $users->firstItem() ?: 0 }}</span> a
            <span class="font-medium">{{ $users->lastItem() ?: 0 }}</span> de
            <span class="font-medium">{{ $users->total() }}</span> resultados
        </div>

        <div class="mt-4 dark:text-white">
            {{ $users->links('pagination::tailwind') }}
        </div>
    </div>
    </div>
</x-app-layout>
