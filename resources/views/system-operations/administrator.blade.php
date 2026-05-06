<x-app-layout>
    @section('page_title', __('custom.system_operations'))

    <div class="space-y-6">
        @if (session('status'))
            <x-alert type="success" :dismissible="true">
                {{ session('status') }}
            </x-alert>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Clear Cache -->
            <x-card class="flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">{{ __('custom.clear_system_cache') }}</h3>
                    <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                        {{ __('custom.clear_system_cache_description') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('system.operations.clear-cache') }}">
                    @csrf
                    <x-primary-button class="w-full justify-center py-2 text-sm">
                        {{ __('custom.clear_cache') }}
                    </x-primary-button>
                </form>
            </x-card>

            <!-- Run Recurring Payments Cron -->
            <x-card class="flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">{{ __('custom.run_recurring_payments_cron') }}</h3>
                    <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                        {{ __('custom.run_recurring_payments_cron_description') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('system.operations.run-cron') }}"
                      onsubmit="return confirm('¿Estás seguro de ejecutar el cron de pagos recurrentes para la fecha seleccionada?');">
                    @csrf
                    <input type="hidden" name="command_signature" value="app:process-recurring-charges">

                    <label class="block text-sm font-medium text-gray-700 mb-2" for="cron_date">
                        {{ __('custom.select_date') }}
                    </label>
                    <input
                        type="date"
                        name="arguments[--date]"
                        id="cron_date"
                        required
                        max="{{ date('Y-m-d') }}"
                        value="{{ date('Y-m-d') }}"
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-600 focus:border-indigo-600 block w-full mb-4"
                    />

                    <x-primary-button class="w-full justify-center py-2 text-sm">
                        {{ __('custom.run_cron') }}
                    </x-primary-button>
                </form>
            </x-card>

            <!-- Sync Discounts -->
            <x-card class="flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">{{ __('custom.sync_discounts_from_shopify') }}</h3>
                    <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                        {{ __('custom.sync_discounts_from_shopify_description') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('system.operations.run-cron') }}"
                      onsubmit="return confirm('¿Estás seguro de ejecutar esta tarea?');">
                    @csrf
                    <input type="hidden" name="command_signature" value="app:sync-discounts-from-shopify">

                    <x-primary-button class="w-full justify-center py-2 text-sm">
                        {{ __('custom.run_cron') }}
                    </x-primary-button>
                </form>
            </x-card>

        </div>
    </div>
</x-app-layout>
