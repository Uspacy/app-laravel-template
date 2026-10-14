<?php

namespace App\Http\Middleware;

use App\Exceptions\JwtDecodeException;
use App\Models\Portal;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ResolvePortal
{
    public const ATTRIBUTE = 'portal';

    public function __construct(
        private JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            \abort(401, 'Token is required');
        }

        try {
            $decoded = $this->jwtService->decode($token, config('uspacy.uspacy_app_client_secret'));
        } catch (JwtDecodeException $ex) {
            \abort(401, $ex->getMessage());
        }

        $domain = Arr::get($decoded, 'headers.domain');

        if (! $domain) {
            \abort(401, 'Domain is required in token header');
        }

        $portal = Portal::where('domain', $domain)->first();

        if (! $portal) {
            \abort(401, 'Portal not found');
        }

        $request->attributes->set(self::ATTRIBUTE, $portal);

        return $next($request);
    }
}
