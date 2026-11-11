<?php

namespace App\Http\Middleware;

use App\Models\Portal;
use App\Services\IntegrationTokenDecoder;
use App\Support\PortalContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ResolvePortalMiddleware
{
    public function __construct(
        private IntegrationTokenDecoder $integrationTokenDecoder,
        private PortalContext $portalContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            \abort(401, 'Token is required');
        }

        $decoded = $this->integrationTokenDecoder->decode($token, $request->route('integration'), 'headers.integration_code');
        $domain = Arr::get($decoded, 'headers.domain');

        if (! $domain) {
            \abort(401, 'Domain is required in token');
        }

        $portal = Portal::whereDomain($domain)
            ->whereIntegrationCode($decoded['integration_code'])
            ->first();

        if (! $portal) {
            \abort(401, 'Portal not found');
        }

        $this->portalContext->set($portal);

        return $next($request);
    }
}
