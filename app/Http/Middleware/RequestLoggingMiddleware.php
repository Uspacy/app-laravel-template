<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $microtimeStart = microtime(true);
        $requestId = $request->header('X-Request-ID') ?: Str::random(12);
        $excludedKeys = [
            'token',
            'refresh_token',
            'access_token',
            'password',
            'client_secret',
        ];
        Log::withContext(['request_id' => $requestId]);
        Log::info('Request start', [
            'accept' => $request->getAcceptableContentTypes(),
            'content-type' => $request->getContentTypeFormat(),
            'content' => $request->except($excludedKeys),
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'agent' => $request->userAgent(),
        ]);

        $response = $next($request);
        $duration = microtime(true) - $microtimeStart;

        Log::info('Request ended', [
            'duration_ms' => round($duration * 1000, 2),
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
