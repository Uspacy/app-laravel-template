<?php

use App\Exceptions\JwtDecodeException;
use App\Http\Middleware\ResolvePortalMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'portal' => ResolvePortalMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn () => true);
        $exceptions->level(JwtDecodeException::class, LogLevel::WARNING);

        $exceptions->render(function (JwtDecodeException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_UNAUTHORIZED,
            );
        });
    })->create();
