<x-guest-layout max-width="max-w-5xl">
    <div
        class="space-y-6"
        x-data="{
            open: false,
            src: '',
            alt: '',
            show(src, alt) { this.src = src; this.alt = alt; this.open = true; },
            close() { this.open = false; }
        }"
        @keydown.escape.window="close()"
    >
        <div class="flex items-start gap-3">
            <div class="mt-0.5 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-200 dark:ring-indigo-500/20">
                <x-heroicon-o-user-minus class="h-6 w-6" />
            </div>
            <div class="space-y-1">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Eliminacion de datos y cuenta</h1>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Esta guia explica como eliminar tu cuenta desde la aplicacion.
                </p>
            </div>
        </div>

        <x-alert type="info" title="Nota" :dismissible="false">
            Por cuestiones logisticas y de auditoria, los registros de ordenes de compra se mantendran.
        </x-alert>

        <div class="rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 bg-white/60 dark:bg-gray-800/40 p-5 sm:p-6">
            <ol class="relative border-l border-gray-200 dark:border-gray-700 space-y-8 pl-6">
                <li class="relative">
                    <span class="absolute -left-[13px] top-0 inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs font-semibold ring-4 ring-white dark:ring-gray-900">
                        1
                    </span>
                    <div class="pl-5">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-7 space-y-2">
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Ingresar a la app</div>
                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                Abre la aplicacion e inicia sesion con tu usuario y contrasena.
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Si no recuerdas tu clave, usa la opcion de recuperacion dentro de la app.
                            </div>
                        </div>
                        <div class="md:col-span-5">
                            <button
                                type="button"
                                class="block w-full text-left"
                                @click="show('{{ asset('images/data-deletion/step-1-login.jpeg') }}', 'Paso 1: ingresar a la app')"
                                aria-label="Ampliar imagen del paso 1"
                            >
                                <img
                                    src="{{ asset('images/data-deletion/step-1-login.jpeg') }}"
                                    alt="Paso 1: ingresar a la app"
                                    class="w-full rounded-xl ring-1 ring-gray-200 dark:ring-gray-700 cursor-zoom-in"
                                    loading="lazy"
                                />
                            </button>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Toca para ampliar.</div>
                        </div>
                    </div>
                    </div>
                </li>

                <li class="relative">
                    <span class="absolute -left-[13px] top-0 inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs font-semibold ring-4 ring-white dark:ring-gray-900">
                        2
                    </span>
                    <div class="pl-5">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-7 space-y-2">
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Ir al perfil</div>
                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                Dirigete al menu desde la barra de navegacion inferior y abre la seccion <span class="font-semibold">Perfil</span>.
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                La barra inferior se encuentra en la parte baja de la pantalla.
                            </div>
                        </div>
                        <div class="md:col-span-5">
                            <button
                                type="button"
                                class="block w-full text-left"
                                @click="show('{{ asset('images/data-deletion/step-2-menu.jpeg') }}', 'Paso 2: menu con opcion eliminar cuenta')"
                                aria-label="Ampliar imagen del paso 2"
                            >
                                <img
                                    src="{{ asset('images/data-deletion/step-2-menu.jpeg') }}"
                                    alt="Paso 2: menu con opcion eliminar cuenta"
                                    class="w-full rounded-xl ring-1 ring-gray-200 dark:ring-gray-700 cursor-zoom-in"
                                    loading="lazy"
                                />
                            </button>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Toca para ampliar.</div>
                        </div>
                    </div>
                    </div>
                </li>

                <li class="relative">
                    <span class="absolute -left-[13px] top-0 inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs font-semibold ring-4 ring-white dark:ring-gray-900">
                        3
                    </span>
                    <div class="pl-5">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-7 space-y-2">
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Eliminar cuenta</div>
                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                Desliza hasta el final de la lista de opciones y selecciona <span class="font-semibold">Eliminar cuenta</span>.
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                La app te pedira una confirmacion final antes de eliminar la cuenta.
                            </div>
                        </div>
                        <div class="md:col-span-5">
                            <button
                                type="button"
                                class="block w-full text-left"
                                @click="show('{{ asset('images/data-deletion/step-3-confirm.jpeg') }}', 'Paso 3: confirmar eliminacion de cuenta')"
                                aria-label="Ampliar imagen del paso 3"
                            >
                                <img
                                    src="{{ asset('images/data-deletion/step-3-confirm.jpeg') }}"
                                    alt="Paso 3: confirmar eliminacion de cuenta"
                                    class="w-full rounded-xl ring-1 ring-gray-200 dark:ring-gray-700 cursor-zoom-in"
                                    loading="lazy"
                                />
                            </button>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Toca para ampliar.</div>
                        </div>
                    </div>
                    </div>
                </li>
            </ol>
        </div>

        <x-alert type="warning" title="Importante" :dismissible="false">
            Al eliminar tu cuenta, perderas el acceso a la app y a tu informacion de perfil. Si necesitas ayuda, crea un ticket en la pagina de soporte.
        </x-alert>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('support.public.create') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5"/>
                <span>Ir a soporte</span>
            </a>
        </div>

        <div
            x-cloak
            x-show="open"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
        >
            <button
                type="button"
                class="absolute inset-0 bg-black/60"
                @click="close()"
                aria-label="Cerrar"
            ></button>

            <div class="relative w-full max-w-4xl">
                <div class="absolute -top-3 -right-3">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-full bg-white text-gray-800 ring-1 ring-gray-200 shadow-sm h-10 w-10 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700"
                        @click="close()"
                        aria-label="Cerrar"
                    >
                        <x-heroicon-o-x-mark class="h-5 w-5"/>
                    </button>
                </div>

                <div class="rounded-2xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900">
                    <img :src="src" :alt="alt" class="w-full h-auto" />
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
