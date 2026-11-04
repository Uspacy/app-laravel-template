<?php

namespace App\Http\Middleware;

use App\Models\Portal;
use App\Services\IntegrationTokenDecoder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ResolvePortalMiddleware
{
    public const PORTAL_REQUEST_ATTRIBUTE = 'portal';

    public function __construct(
        private IntegrationTokenDecoder $integrationTokenDecoder
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            \abort(401, 'Token is required');
        }

        $decoded = $this->integrationTokenDecoder->decode($token, $request->route('integration'), 'claims.integration_code');
        $domain = Arr::get($decoded, 'claims.domain');

        if (! $domain) {
            \abort(401, 'Domain is required in token claims');
        }

        $portal = Portal::whereDomain($domain)
            ->whereIntegrationCode($decoded['integration_code'])
            ->first();

        if (! $portal) {
            \abort(401, 'Portal not found');
        }

        $request->attributes->set(self::PORTAL_REQUEST_ATTRIBUTE, $portal);

        return $next($request);
    }
}
