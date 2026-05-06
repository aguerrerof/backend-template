<?php

use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\Dashboard\FulfillmentController;
use App\Http\Controllers\Dashboard\GraphQLSandboxController;
use App\Http\Controllers\Dashboard\LogisticProvidersController;
use App\Http\Controllers\Dashboard\OrdersController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\SupportTicketController;
use App\Http\Controllers\Dashboard\SystemOperationsController;
use App\Http\Controllers\Dashboard\UsersController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Support\SupportTicketPublicController;
use App\Http\Controllers\Webhooks\LAARCourierWebhookController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

Route::get('/payments/challenge', [PaymentsController::class, 'showChallenge']);
Route::get('/payments/3ds/callback', [PaymentsController::class, 'complete3dsValidation']);
Route::prefix('webhooks')->group(function () {
    Route::post('/laar/orders', [LAARCourierWebhookController::class, 'handle']);
});
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('profile.edit')
        : redirect()->route('login');
});

Route::get('/support', [SupportTicketPublicController::class, 'create'])
    ->middleware('no.cache')
    ->name('support.public.create');
Route::post('/support', [SupportTicketPublicController::class, 'store'])
    ->middleware('no.cache')
    ->middleware('throttle:support-ticket-submit')
    ->name('support.public.store');
Route::get('/support/thanks', [SupportTicketPublicController::class, 'thanks'])
    ->middleware('no.cache')
    ->name('support.public.thanks');

Route::get('/data-deletion', function () {
    return view('public.data-deletion');
})->middleware('no.cache')->name('public.data-deletion');

Route::middleware(['auth', 'check.web.session'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::group(['middleware' => ['auth', 'verified', 'check.web.session']], function () {
    Route::get('/logistic-providers/autocomplete', [LogisticProvidersController::class, 'autocomplete'])
        ->name('logistic-providers.autocomplete');
    Route::get('/orders', [OrdersController::class, 'index'])->name('orders');
    Route::get('/orders/{id}', [OrdersController::class, 'show'])->name('orders.show');
    Route::get('/orders/{orderId}/fulfillments/create', [FulfillmentController::class, 'create'])
        ->name('orders.create-fulfillment');
    Route::post('/fulfillments', [FulfillmentController::class, 'store'])
        ->name('fulfillments.store');
    Route::get('/fulfillments/{id}/', [FulfillmentController::class, 'show'])
        ->name('fulfillments.show');
    Route::get('/fulfillments/{id}/delivery-note', [FulfillmentController::class, 'printDeliveryNote'])
        ->name('fulfillments.delivery-note');
    Route::put('/fulfillments/{id}', [FulfillmentController::class, 'update'])
        ->name('fulfillments.update');
    Route::post('/fulfillments/{id}/cancel', [FulfillmentController::class, 'cancel'])
        ->name('fulfillments.cancel');
});
Route::group(['middleware' => ['auth', 'verified', 'check.admin', 'check.web.session']], function () {
    Route::get('/activity-logs', [ActivityLogsController::class, 'index'])->name('activity-logs.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::delete('/settings/{id}', [SettingsController::class, 'delete'])->name(
        'settings.delete',
    );
    Route::get('/settings/{id}/edit', [SettingsController::class, 'edit'])->name(
        'settings.edit',
    );
    Route::put('/settings/{id}', [SettingsController::class, 'update'])->name(
        'settings.update',
    );
    Route::get('/graphql-sandbox', [GraphQLSandboxController::class, 'index'])->name('graphql-sandbox.index');
    Route::post('/graphql-sandbox', [GraphQLSandboxController::class, 'execute'])->name('graphql-sandbox.execute');
    Route::get('/recurring-orders', [OrdersController::class, 'listRecurringOrders'])->name('recurring-orders.index');
    Route::get('/recurring-orders/{id}', [OrdersController::class, 'showRecurringOrder'])->name(
        'recurring-orders.show',
    );
    Route::get('system-operations', [SystemOperationsController::class, 'index'])->name('system-operations.index');
    Route::post('/system-operations/clear-cache', [SystemOperationsController::class, 'clearCache'])->name(
        'system.operations.clear-cache',
    );
    Route::post('/system-operations/cron', [SystemOperationsController::class, 'runCron'])
        ->name('system.operations.run-cron');
    Route::get('logs', [LogViewerController::class, 'index'])->name('logs');
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update');
    Route::get('/users/{id}/deactivate', [UsersController::class, 'deactivate'])->name('users.deactivate');
    Route::get('/users/{id}/activate', [UsersController::class, 'activate'])->name('users.activate');
    Route::get('/logistic-providers', [LogisticProvidersController::class, 'index'])->name('logistic-providers');
    Route::get('/logistic-providers/create', [LogisticProvidersController::class, 'create'])->name(
        'logistic-providers.create',
    );
    Route::post('/logistic-providers', [LogisticProvidersController::class, 'store'])
        ->name('logistic-providers.store');
    Route::get('/logistic-providers/{id}/edit', [LogisticProvidersController::class, 'edit'])->name(
        'logistic-providers.edit',
    );
    Route::put('/logistic-providers/{id}', [LogisticProvidersController::class, 'update'])->name(
        'logistic-providers.update',
    );
    Route::delete('/logistic-providers/{id}', [LogisticProvidersController::class, 'delete'])->name(
        'logistic-providers.delete',
    );

    Route::get('/support/tickets', [SupportTicketController::class, 'index'])
        ->name('support-tickets.index');
    Route::get('/support/tickets/create', [SupportTicketController::class, 'create'])
        ->name('support-tickets.create');
    Route::post('/support/tickets', [SupportTicketController::class, 'store'])
        ->name('support-tickets.store');
    Route::get('/support/tickets/{ticket}', [SupportTicketController::class, 'show'])
        ->name('support-tickets.show');
});
require __DIR__ . '/auth.php';
