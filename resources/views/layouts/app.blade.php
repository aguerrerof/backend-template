<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = (stored === 'dark' || stored === 'light') ? stored : (prefersDark ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', theme === 'dark');
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ mobileOpen: false }" class="min-h-screen bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans antialiased flex">

<!-- Sidebar -->
@include('layouts.navigation')

<!-- Main content -->
<div class="flex-1 flex flex-col overflow-hidden min-h-0">
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <!-- Mobile hamburger -->
            <button
                type="button"
                @click="mobileOpen = true"
                class="sm:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800"
                aria-label="Open sidebar"
            >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            </button>

            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 truncate">@yield('page_title', 'Dashboard')</h2>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2">
            @hasSection('page_actions')
                @yield('page_actions')
            @endif

            <button
                type="button"
                x-data="{ dark: document.documentElement.classList.contains('dark') }"
                x-init="window.addEventListener('theme-changed', (e) => { dark = e.detail.theme === 'dark' })"
                @click="window.__toggleTheme?.(); dark = document.documentElement.classList.contains('dark')"
                :aria-pressed="dark.toString()"
                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 transition"
                aria-label="Cambiar modo oscuro"
                title="Modo oscuro"
            >
                <svg x-cloak x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a7 7 0 1010.586 10.586z" />
                </svg>
                <svg x-cloak x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 2.47a1 1 0 011.415 0l.707.707a1 1 0 11-1.414 1.414l-.708-.707a1 1 0 010-1.414zM18 9a1 1 0 100 2h-1a1 1 0 100-2h1zM15.636 15.636a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM11 18a1 1 0 10-2 0v-1a1 1 0 102 0v1zM6.364 15.636a1 1 0 00-1.414 0l-.707.707a1 1 0 001.414 1.414l.707-.707a1 1 0 000-1.414zM3 11a1 1 0 100-2H2a1 1 0 100 2h1zm2.05-6.05a1 1 0 010 1.414l-.707.707A1 1 0 112.929 5.657l.707-.707a1 1 0 011.414 0zM10 6a4 4 0 100 8 4 4 0 000-8z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 sm:py-6 min-h-0">
        <div class="mx-auto w-full max-w-7xl">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
