<?php

use App\Exceptions\BasicException;
use App\Helpers\LogHelper;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            __DIR__ . '/../routes/V1/Private/Auth.php',
            __DIR__ . '/../routes/V1/Private/Payment.php',
            __DIR__ . '/../routes/V1/Public/Public.php',
            __DIR__ . '/../routes/V1/Public/Webhook.php',
        ],
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/saude-server-check',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'security' => \App\Http\Middleware\KeySecurity::class,
        ]);
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            // \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if ($exceptions instanceof BasicException) {
            $exceptions->render();
        }

        $exceptions->render(function (Throwable $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage(),
                "authorization" => [
                    "status" => "reproved"
                ]
            ];

            return response($response, 500);
        });
    })->create();
