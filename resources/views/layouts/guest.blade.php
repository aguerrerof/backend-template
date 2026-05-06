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

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 sm:py-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="#">
                    <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl bg-indigo-600 text-white flex items-center justify-center text-3xl font-bold shadow-lg">
                        {{ strtoupper(substr(config('app.name', 'App'), 0, 2)) }}
                    </div>
                </a>
            </div>

            <div class="w-full max-w-md mt-4 flex justify-end">
                <button
                    type="button"
                    x-data="{ dark: document.documentElement.classList.contains('dark') }"
                    x-init="window.addEventListener('theme-changed', (e) => { dark = e.detail.theme === 'dark' })"
                    @click="window.__toggleTheme?.(); dark = document.documentElement.classList.contains('dark')"
                    :aria-pressed="dark.toString()"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-100 dark:focus:ring-offset-gray-900 transition"
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

            <div class="w-full {{ $maxWidth }} mt-2 px-5 py-5 sm:px-6 sm:py-6 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
