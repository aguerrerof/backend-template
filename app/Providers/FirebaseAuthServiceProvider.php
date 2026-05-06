<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;

class FirebaseAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Auth::class, function ($app) {
            try {
                $factory = (new Factory())
                    ->withServiceAccount(
                        storage_path('app/firebase/credentials.json')
                    );
                return $factory->createAuth();
            } catch (\Exception $e) {
                Log::warning(
                    'Could not get Firebase service account from Secret Manager. ' .
                    'Falling back to implicit credentials. Error: ' .
                    $e->getMessage()
                );
            }
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
