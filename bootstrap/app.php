<?php

use App\Http\Middleware\TrackNeonUsageEstimate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [TrackNeonUsageEstimate::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (QueryException $exception): void {
            Log::error('Database query exception context', [
                'sqlstate' => $exception->errorInfo[0] ?? (string) $exception->getCode(),
                'driver_code' => $exception->errorInfo[1] ?? null,
                'connection' => $exception->getConnectionName(),
                'route' => request()->route()?->getName(),
                'method' => request()->method(),
                'path' => request()->path(),
                'transaction_level' => DB::connection($exception->getConnectionName())->transactionLevel(),
            ]);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
