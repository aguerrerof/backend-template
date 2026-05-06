<div>
    <!-- Desktop sidebar -->
    <aside
        x-data="{ open: true, logsOpen: false, adminOpen: false }"
        x-init="
            open = JSON.parse(localStorage.getItem('sidebarOpen') ?? 'true');
            logsOpen = false;
            $watch('open', value => {
                localStorage.setItem('sidebarOpen', JSON.stringify(value));
                if (!value) logsOpen = false;
                if (!value) adminOpen = false;
            });
        "
        :class="open ? 'w-64' : 'w-20'"
        class="hidden sm:flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 overflow-hidden h-screen shadow-sm"
    >
        <!-- Brand + toggle -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800">
            <a href="{{ route('orders') }}" class="flex items-center space-x-2">
                <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-xs font-bold">
                    {{ strtoupper(substr(config('app.name', 'App'), 0, 2)) }}
                </div>
                <span x-show="open" class="font-semibold text-lg text-gray-800 dark:text-gray-100">{{ config('app.name') }}</span>
            </a>
            <button
                type="button"
                @click="open = !open"
                class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                :aria-expanded="open.toString()"
                aria-label="Toggle sidebar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- User info + logout -->
        <div class="border-b border-gray-200 dark:border-gray-800 p-3">
            <div
                class="flex items-center gap-3 rounded-lg px-3 py-2"
                :class="{'justify-center': !open}"
                title="{{ Auth::user()?->name ?? '' }}"
            >
                <a
                    href="{{ route('profile.edit') }}"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                    aria-label="{{ __('custom.edit_profile') }}"
                    title="{{ __('custom.edit_profile') }}"
                >
                    <x-heroicon-o-user class="h-5 w-5"/>
                </a>
                <div x-show="open" x-transition class="min-w-0">
                    <div class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ Auth::user()?->name }}</div>
                    <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()?->email }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-3"
                  onsubmit="return confirm('¿Seguro que deseas cerrar sesión?')">
                @csrf
                <button
                    type="submit"
                    class="group flex items-center w-full gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-200 transition"
                    :class="{'justify-center': !open}"
                    title="{{ __('custom.logout') }}"
                >
                    <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-red-700 dark:group-hover:text-red-200"/>
                    <span x-show="open" x-transition class="whitespace-nowrap">{{ __('custom.logout') }}</span>
                </button>
            </form>
        </div>


        <!-- Links -->
        <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-1">
            @php
                $isAdmin = Auth::user()?->isAdmin() ?? false;

                $mainLinks = [
                    ['route' => 'orders', 'icon' => 'shopping-bag', 'label' => __('custom.orders')],
                    ['route' => 'recurring-orders.index', 'icon' => 'arrow-path', 'label' => __('custom.recurring_orders')],
                ];

                $logLinks = [
                    ['route' => 'logs', 'icon' => 'clipboard-document-list', 'label' => __('custom.error_logs')],
                    ['route' => 'activity-logs.index', 'icon' => 'clipboard-document-list', 'label' => __('custom.activity_logs')],
                ];

                $adminLinks = [
                    ['route' => 'users', 'icon' => 'users', 'label' => __('custom.users')],
                    ['route' => 'logistic-providers', 'icon' => 'truck', 'label' => __('custom.logistic-providers')],
                    ['route' => 'system-operations.index', 'icon' => 'cog-6-tooth', 'label' => __('custom.system_operations')],
                    ['route' => 'graphql-sandbox.index', 'icon' => 'code-bracket', 'label' => __('custom.graphql_sandbox_shopify')],
                    ['route' => 'settings.index', 'icon' => 'cog-6-tooth', 'label' => __('custom.app_mobile_settings')],
                    ['route' => 'support-tickets.index', 'icon' => 'chat-bubble-left-right', 'label' => __('custom.support')],
                ];
            @endphp

            @foreach ($mainLinks as $link)
                @php
                    $isActive = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*');
                @endphp
                <a href="{{ route($link['route']) }}"
                   class="{{ $isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                   :class="{'justify-center': !open}"
                   title="{{ $link['label'] }}"
                   @if($isActive) aria-current="page" @endif
                >
                    <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="{{ $isActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200' }} w-5 h-5"/>
                    <span x-show="open" x-transition class="whitespace-nowrap">{{ $link['label'] }}</span>
                </a>
            @endforeach

            @if ($isAdmin)
                <div class="pt-3">
                    <button
                        type="button"
                        @click="if (open) adminOpen = !adminOpen"
                        class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition"
                        :class="{'justify-center': !open}"
                        :aria-expanded="(open && adminOpen).toString()"
                        :aria-disabled="(!open).toString()"
                        aria-controls="sidebar-admin"
                        title="Administración"
                    >
                        <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200"/>
                        <span x-show="open" x-transition class="flex-1 text-left whitespace-nowrap">Administración</span>
                        <span x-show="open" x-transition class="inline-flex items-center">
                            <x-heroicon-o-chevron-down class="h-4 w-4 text-gray-400 dark:text-gray-500" x-bind:class="adminOpen ? 'rotate-180' : ''"/>
                        </span>
                    </button>

                    <div
                        id="sidebar-admin"
                        x-cloak
                        x-show="open && adminOpen"
                        x-transition
                        class="mt-1 space-y-1"
                    >
                        @foreach ($adminLinks as $link)
                            @php
                                $isActive = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*');
                            @endphp
                            <a href="{{ route($link['route']) }}"
                               class="{{ $isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                               :class="{'justify-center': !open}"
                               title="{{ $link['label'] }}"
                               @if($isActive) aria-current="page" @endif
                            >
                                <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="{{ $isActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200' }} w-5 h-5"/>
                                <span x-show="open" x-transition class="whitespace-nowrap">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-3">
                <button
                    type="button"
                    @click="if (open) logsOpen = !logsOpen"
                    class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition"
                    :class="{'justify-center': !open}"
                    :aria-expanded="(open && logsOpen).toString()"
                    :aria-disabled="(!open).toString()"
                    aria-controls="sidebar-logs"
                    title="Logs"
                >
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200"/>
                    <span x-show="open" x-transition class="flex-1 text-left whitespace-nowrap">Logs</span>
                    <span x-show="open" x-transition class="inline-flex items-center">
                        <x-heroicon-o-chevron-down class="h-4 w-4 text-gray-400 dark:text-gray-500" x-bind:class="logsOpen ? 'rotate-180' : ''"/>
                    </span>
                </button>

                <div
                    id="sidebar-logs"
                    x-cloak
                    x-show="open && logsOpen"
                    x-transition
                    class="mt-1 space-y-1"
                >
                    @foreach ($logLinks as $link)
                        @php
                            $isActive = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*');
                        @endphp
                        <a href="{{ route($link['route']) }}"
                           class="{{ $isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                           :class="{'justify-center': !open}"
                           title="{{ $link['label'] }}"
                           @if($isActive) aria-current="page" @endif
                        >
                            <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="{{ $isActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200' }} w-5 h-5"/>
                            <span x-show="open" x-transition class="whitespace-nowrap">{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>
    </aside>

    <!-- Mobile sidebar -->
    <div x-show="mobileOpen" class="sm:hidden fixed inset-0 z-40 flex" x-cloak x-transition.opacity>
        <div @click="mobileOpen = false" class="fixed inset-0 bg-black/50"></div>
        <aside
            x-data="{ logsOpen: false, adminOpen: false }"
            class="relative flex-1 flex flex-col max-w-xs w-full bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 h-screen shadow-sm"
            x-transition
        >
            <!-- Brand + close -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                <a href="{{ route('orders') }}" class="flex items-center space-x-2">
                    <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-xs font-bold">
                        {{ strtoupper(substr(config('app.name', 'App'), 0, 2)) }}
                    </div>
                    <span class="font-semibold text-lg text-gray-800 dark:text-gray-100">{{ config('app.name') }}</span>
                </a>
                <button
                    type="button"
                    @click="mobileOpen = false"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                    aria-label="Close sidebar"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- User info + logout -->
            <div class="border-b border-gray-200 dark:border-gray-800 p-3">
                <div
                    class="flex items-center gap-3 rounded-lg px-3 py-2"
                    title="{{ Auth::user()?->name ?? '' }}"
                >
                    <a
                        href="{{ route('profile.edit') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                        aria-label="{{ __('custom.edit_profile') }}"
                        title="{{ __('custom.edit_profile') }}"
                    >
                        <x-heroicon-o-user class="h-5 w-5"/>
                    </a>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ Auth::user()?->name }}</div>
                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()?->email }}</div>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="mt-2"
                    onsubmit="return confirm('¿Seguro que deseas cerrar sesión?')"
                >
                    @csrf
                    <button
                        type="submit"
                        class="group flex items-center w-full gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-200 transition"
                        title="{{ __('custom.logout') }}"
                    >
                        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-red-700 dark:group-hover:text-red-200"/>
                        <span class="whitespace-nowrap">{{ __('custom.logout') }}</span>
                    </button>
                </form>
            </div>

            <!-- Links -->
            <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-1">
                @foreach ($mainLinks as $link)
                    @php
                        $isActive = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*');
                    @endphp
                    <a href="{{ route($link['route']) }}"
                       class="{{ $isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                       @if($isActive) aria-current="page" @endif
                    >
                        <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="{{ $isActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200' }} w-5 h-5"/>
                        <span class="whitespace-nowrap">{{ $link['label'] }}</span>
                    </a>
                @endforeach

                @if ($isAdmin)
                    <div class="pt-3">
                        <button
                            type="button"
                            @click="adminOpen = !adminOpen"
                            class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition"
                            :aria-expanded="adminOpen.toString()"
                            aria-controls="mobile-sidebar-admin"
                        >
                            <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200"/>
                            <span class="flex-1 text-left whitespace-nowrap">Administración</span>
                            <x-heroicon-o-chevron-down class="h-4 w-4 text-gray-400 dark:text-gray-500" x-bind:class="adminOpen ? 'rotate-180' : ''"/>
                        </button>

                        <div
                            id="mobile-sidebar-admin"
                            x-cloak
                            x-show="adminOpen"
                            x-transition
                            class="mt-1 space-y-1"
                        >
                            @foreach ($adminLinks as $link)
                                @php
                                    $isActive = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*');
                                @endphp
                                <a href="{{ route($link['route']) }}"
                                   class="{{ $isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                                   @if($isActive) aria-current="page" @endif
                                >
                                    <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="{{ $isActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200' }} w-5 h-5"/>
                                    <span class="whitespace-nowrap">{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pt-3">
                    <button
                        type="button"
                        @click="logsOpen = !logsOpen"
                        class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition"
                        :aria-expanded="logsOpen.toString()"
                        aria-controls="mobile-sidebar-logs"
                    >
                        <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200"/>
                        <span class="flex-1 text-left whitespace-nowrap">Logs</span>
                        <x-heroicon-o-chevron-down
                            class="h-4 w-4 text-gray-400 dark:text-gray-500"
                            x-bind:class="logsOpen ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        id="mobile-sidebar-logs"
                        x-cloak
                        x-show="logsOpen"
                        x-transition
                        class="mt-1 space-y-1"
                    >
                        @foreach ($logLinks as $link)
                            @php
                                $isActive = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*');
                            @endphp
                            <a href="{{ route($link['route']) }}"
                               class="{{ $isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                               @if($isActive) aria-current="page" @endif
                            >
                                <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="{{ $isActive ? 'text-indigo-700 dark:text-indigo-200' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200' }} w-5 h-5"/>
                                <span class="whitespace-nowrap">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </nav>
        </aside>
    </div>
</div>
