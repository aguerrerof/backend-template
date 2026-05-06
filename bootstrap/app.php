<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\NoCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/profile');

        $middleware->api(prepend: [
            ForceJsonResponse::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'verify.firebase.token' => \App\Http\Middleware\VerifyFirebaseToken::class,
            'decrypt.request' => \App\Http\Middleware\DecryptRequest::class,
            'check.admin' => \App\Http\Middleware\CheckAdmin::class,
            'check.web.session' => \App\Http\Middleware\CheckWebSession::class,
            'no.cache' => NoCache::class,
        ]);
    })->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return response()->view('errors.419', [], 419);
        });
    })
    ->withProviders([
        BladeUI\Icons\BladeIconsServiceProvider::class,
        BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
    ])
    ->create();
