<?php

use App\Http\Middleware\CaptureAttribution;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            CaptureAttribution::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Let the browser load the dependency-free error view after SPA navigation.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if ($request->headers->has('X-Inertia')
                && $request->isMethod('GET')
                && ! $request->expectsJson()
                && in_array($response->getStatusCode(), [404, 500], true)) {
                return response('', 409, ['X-Inertia-Location' => $request->fullUrl()]);
            }

            return $response;
        });
    })->create();
