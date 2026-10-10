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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/payroll/attendance/punch',
            'api/attendance/*',
            'iclock/*',
        ]);

        $middleware->alias([
            'module.permission' => \App\Http\Middleware\CheckModulePermission::class,
            'owner' => \App\Http\Middleware\OwnerOnly::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // AI token exhaustion is a user-fixable condition, not a crash, so the
        // scanning endpoints want a readable message they can surface in their
        // own error UI. 402 (Payment Required) keeps it distinct from a 500.
        $exceptions->render(function (\App\Exceptions\AiTokenLimitExceededException $e, $request) {
            \Illuminate\Support\Facades\Log::info('AI Token limit exception rendered');
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'title' => 'AI Token Limit Reached',
                    'message' => $e->getMessage(),
                    'data' => [],
                ], 402);
            }

            return null;
        });
    })->create();
