<?php

namespace App\Providers;

use App\Events\FulfillmentCancelled;
use App\Events\FulfillmentCreated;
use App\Listeners\HandleFulfillmentCancelled;
use App\Listeners\HandleFulfillmentCreated;
use App\Models\Fulfillment;
use App\Models\OrderPayment;
use App\Models\User;
use App\Observers\FulfillmentObserver;
use App\Observers\OrderPaymentObserver;
use App\Observers\UserObserver;
use App\Services\Authentication\AuthenticationService;
use App\Services\Authentication\AuthService;
use App\Services\Diagnostic\AnimalHealthInsightService;
use App\Services\Diagnostic\ChatGptAnimalHealthInsightService;
use App\Services\Eloquent\OrderEloquentService;
use App\Services\Eloquent\OrderService;
use App\Services\Eloquent\RecurringOrderEloquentService;
use App\Services\Eloquent\RecurringOrderService;
use App\Services\Finance\Billing\BillingProviderInterface;
use App\Services\Finance\Billing\UvaCloudBillingService;
use App\Services\Google\FirebasePushNotificationService;
use App\Services\Google\PushNotificationService;
use App\Services\PaymentGateways\PagoPluxClient;
use App\Services\PaymentGateways\PagoPluxService;
use App\Services\PaymentGateways\PaymentGatewayService;
use App\Services\Pricing\PriceService;
use App\Services\Pricing\PricingService;
use App\Services\Shop\AddressService;
use App\Services\Shop\ShopifyAddressService;
use App\Services\Shop\ShopifyService;
use App\Services\Shop\ShopService;
use App\Services\ShoppingCart\CartService;
use App\Services\ShoppingCart\ShoppingCartService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // PagoPlux
        $this->app->singleton(PagoPluxClient::class, function ($app) {
            $paymentGatewayCredentials = $this->getPaymentGatewayCredentials();
            return new PagoPluxClient(
                $paymentGatewayCredentials['BASE_URL'],
                $paymentGatewayCredentials['CLIENT_ID'],
                $paymentGatewayCredentials['SECRET'],
                $paymentGatewayCredentials['PUBLIC_KEY'],
                $paymentGatewayCredentials['ESTABLISHMENT_ID'],
            );
        });
        $this->app->bind(PaymentGatewayService::class, PagoPluxService::class);

        // Shopify
        $this->app->bind(ShopService::class, ShopifyService::class);
        $this->app->singleton(ShopifyService::class, function () {
            return new ShopifyService(
                new Client([
                    'base_uri' => config('services.shopify.store_url'),
                ]),
                app()->get(PaymentGatewayService::class),
                config('services.shopify.access_token', ''),
                config('services.shopify.api_version', ''),
                config('services.shopify.api_token', ''),
            );
        });
        $this->app->bind(AddressService::class, function ($app) {
            return new ShopifyAddressService($app->make(ShopifyService::class));
        });

        $this->app->bind(RecurringOrderService::class, RecurringOrderEloquentService::class);
        $this->app->bind(ShoppingCartService::class, CartService::class);
        $this->app->bind(PricingService::class, PriceService::class);
        $this->app->bind(AuthenticationService::class, AuthService::class);
        $this->app->bind(OrderService::class, OrderEloquentService::class);
        $this->app->singleton(PushNotificationService::class, function ($app) {
            $factory = (new Factory())
                ->withServiceAccount(storage_path('app/firebase/credentials.json'));

            $messaging = $factory->createMessaging();

            return new FirebasePushNotificationService($messaging);
        });
        $this->app->bind(
            AnimalHealthInsightService::class,
            ChatGptAnimalHealthInsightService::class
        );
        $this->app->bind(BillingProviderInterface::class, UvaCloudBillingService::class);
    }

    public function boot(): void
    {
        Config::set('app.timezone', 'America/Guayaquil');
        date_default_timezone_set(config('app.timezone'));
        Carbon::setLocale('es');
        if (!$this->app->runningInConsole() && app()->isProduction()) {
            URL::forceScheme('https');
        }

        User::observe(UserObserver::class);
        Fulfillment::observe(FulfillmentObserver::class);
        OrderPayment::observe(OrderPaymentObserver::class);

        Event::listen(
            FulfillmentCreated::class,
            [HandleFulfillmentCreated::class, 'handle'],
        );
        Event::listen(
            FulfillmentCancelled::class,
            [HandleFulfillmentCancelled::class, 'handle'],
        );
        RateLimiter::for('support-ticket-submit', function (Request $request) {
            $email = strtolower(trim((string)$request->input('guest_email', '')));
            $identifier = $email !== '' ? $email : (string)$request->ip();

            $response = function () use ($request) {
                $message = __('custom.support_rate_limited');
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 429);
                }

                return back()
                    ->withErrors(['support' => $message])
                    ->withInput();
            };

            return [
                Limit::perHour(3)->by('support-ticket:hour:' . $identifier)->response($response),
                Limit::perDay(10)->by('support-ticket:day:' . $identifier)->response($response),
            ];
        });
    }

    private function getPaymentGatewayCredentials(): array
    {
        $content = file_get_contents(storage_path(config('services.payment_gateway.credentials')));
        return json_decode($content, true);
    }
}
