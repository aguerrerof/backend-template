<?php

use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\AddressesController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CardsController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CitiesController;
use App\Http\Controllers\CollectionsController;
use App\Http\Controllers\CustomersBillingInformationController;
use App\Http\Controllers\Diagnostic\HealthInsightController;
use App\Http\Controllers\DiscountsController;
use App\Http\Controllers\FulfillmentsController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PushNotificationTestController;
use App\Http\Controllers\RecurrenceFrequenciesController;
use App\Http\Controllers\RecurringChargesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShippingRatesController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\Webhooks\LAARCourierWebhookController;
use App\Http\Controllers\Webhooks\UrbanoCourierWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'index']);
Route::post('/tasks/charges/recurring', [RecurringChargesController::class, '__invoke']);
Route::get('/collection', [CollectionsController::class, 'fetchCollection']);
Route::get('/collections/{id}/products', [CollectionsController::class, 'getProductsCollection'])
    ->where('id', '.*');
Route::post('/tasks/charges/recurring', [RecurringChargesController::class, '__invoke']);
Route::post('/cart/subtotals', [ShopController::class, 'generateSubtotals']);
Route::get('/products/multiple', [ProductsController::class, 'getProductsInformation']);
Route::get('/products/search', [ProductsController::class, 'searchProduct']);
Route::get('/products/variants/{id}', [ProductsController::class, 'getProductVariants'])
    ->where('id', '.*');
Route::get('/products/recurrence-offer', [ProductsController::class, 'getProductOfferRecurrence']);
Route::get('/products/variants', [ProductsController::class, 'getProductVariantsInformation']);
Route::get('/users', [AuthenticationController::class, 'verify']);
Route::get('/recurrence-frequencies', [RecurrenceFrequenciesController::class, 'index']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/cities', [CitiesController::class, 'index']);
Route::get('/products/{id}/inventory', [ProductsController::class, 'getInventoryAvailable'])
    ->where('id', '.*');
Route::middleware(['verify.firebase.token', 'decrypt.request'])->group(function () {
    Route::post('/cards', [CardsController::class, 'register']);
});
Route::middleware(['verify.firebase.token'])->group(function () {
    Route::post('/push-notifications', PushNotificationTestController::class);
    Route::post('/cards/otp', [CardsController::class, 'completeRegistrationWithOtp']);
    Route::post('/payments', [PaymentsController::class, 'store']);
    Route::delete('/cards/{token}', [CardsController::class, 'delete']);
    Route::get('/cards', [CardsController::class, 'index']);
    Route::post('/orders', [OrdersController::class, 'createOrder']);
    Route::get('/orders/{id}', [OrdersController::class, 'show']);
    Route::put('/orders/{id}', [OrdersController::class, 'updateOrder']);
    Route::get('/orders', [OrdersController::class, 'index']);
    Route::put('/recurring-orders/{id}', [OrdersController::class, 'updateRecurringOrder']);
    Route::get('/recurring-orders', [OrdersController::class, 'getRecurringOrdersByUser']);
    Route::delete('/recurring-orders/{id}', [OrdersController::class, 'deleteRecurringOrder']);
    Route::get('/billing-information', [CustomersBillingInformationController::class, 'index']);
    Route::post('/billing-information', [CustomersBillingInformationController::class, 'store']);
    Route::put('/billing-information/{id}', [CustomersBillingInformationController::class, 'update']);
    Route::delete('/billing-information/{id}', [CustomersBillingInformationController::class, 'delete']);
    Route::get('/shipping-rates', [ShippingRatesController::class, 'index']);
    Route::get('/discounts', [DiscountsController::class, 'index']);
    Route::get('/cart', [ShoppingCartController::class, 'getCart']);
    Route::post('/cart', [ShoppingCartController::class, 'add']);
    Route::put('/cart/{id}', [ShoppingCartController::class, 'modify']);
    Route::delete('/cart/items', [ShoppingCartController::class, 'clearCart']);
    Route::delete('/cart/{id}', [ShoppingCartController::class, 'delete']);
    Route::post('/checkout', [ShopController::class, 'generateCheckout']);
    Route::post('/users', [AuthenticationController::class, 'authenticate']);
    Route::get('/shopify/addresses', [AddressesController::class, 'index']);
    Route::post('/shopify/addresses', [AddressesController::class, 'store']);
    Route::put('/shopify/addresses', [AddressesController::class, 'update']);
    Route::delete('/shopify/addresses/{address_id}', [AddressesController::class, 'destroy'])
        ->where('address_id', '.*');
    Route::post('/activity-logs', [ActivityLogsController::class, 'store']);
    Route::delete('/users', [AuthenticationController::class, 'delete']);
    Route::post('/users/reactivate', [AuthenticationController::class, 'reactivate']);
    Route::post('/users/devices', [AuthenticationController::class, 'linkNewDevice']);
    Route::delete('/users/devices/{device_id}', [AuthenticationController::class, 'unlinkDevice']);
    Route::get('/orders/{id}/fulfillments', [FulfillmentsController::class, 'getByOrder']);
    Route::get('/fulfillments/{id}', [FulfillmentsController::class, 'show']);
    Route::get('/reorder/suggestions', [ProductsController::class, 'getReorderableProducts']);
    Route::post('/orders/{id}/payments', [OrdersController::class, 'retryPayment']);
    Route::middleware('throttle:5,1')->post(
        '/quiz/health-insight',
        [HealthInsightController::class, 'analyze']
    );
});
Route::prefix('webhooks')->group(function () {
    Route::post('/laar/orders', [LAARCourierWebhookController::class, 'handle']);
    Route::post('/urbano/orders', [UrbanoCourierWebhookController::class, 'handle']);
});
Route::prefix('catalogs')->group(function () {
    Route::get('/species', [CatalogController::class, 'species']);
    Route::get('/breeds/{speciesKey}', [CatalogController::class, 'breeds']);
    Route::get('/symptoms/{speciesKey}', [CatalogController::class, 'symptoms']);
    Route::get('/medical-conditions', [CatalogController::class, 'medicalConditions']);
});
