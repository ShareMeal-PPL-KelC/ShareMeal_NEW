<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware::class . '\RoleMiddleware',
            'profile.complete' => \App\Http\Middleware\EnsureProfileIsComplete::class,
        ]);

        if (env('DB_DATABASE') === 'sharemeal_testing') {
            $middleware->validateCsrfTokens(except: [
                'consumer/checkout',
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ukuran data/file yang diunggah terlalu besar. Maksimal total ukuran adalah 2 MB.'
                ], 413);
            }
            return back()->with('error', 'Ukuran data/file yang Anda unggah terlalu besar. Maksimal total ukuran adalah 2 MB.')->withInput();
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.'
                ], 429);
            }
            return back()->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.')->withInput();
        });
    })->create();
