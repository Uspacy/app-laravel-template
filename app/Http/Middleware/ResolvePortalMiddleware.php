<?php

namespace App\Http\Middleware;

use App\Models\Portal;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ResolvePortalMiddleware
{
    public const PORTAL_REQUEST_ATTRIBUTE = 'portal';

    public function __construct(
        private JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            \abort(401, 'Token is required');
        }

        $decoded = $this->jwtService->decode($token, config('uspacy.app_client_secret'));
        $domain = Arr::get($decoded, 'claims.domain');

        if (! $domain) {
            \abort(401, 'Domain is required in token header');
        }

        $portal = Portal::where('domain', $domain)->first();

        if (! $portal) {
            \abort(401, 'Portal not found');
        }

        $request->attributes->set(self::PORTAL_REQUEST_ATTRIBUTE, $portal);

        return $next($request);
    }
}
