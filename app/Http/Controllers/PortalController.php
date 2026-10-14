<?php

namespace App\Http\Controllers;

use App\Http\Requests\Portals\InstallPortalRequest;
use App\Services\JwtService;
use App\Services\Portal\PortalInstaller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class PortalController extends Controller
{
    public function __construct(
        private PortalInstaller $portalInstaller,
        private JwtService $jwtService
    ) {}

    public function install(InstallPortalRequest $request)
    {
        $token = $request->bearerToken();

        if (! $token) {
            \abort(401, 'Token is required');
        }

        $decoded = $this->jwtService->decode($token, config('uspacy.uspacy_app_client_secret'));
        $clientId = Arr::get($decoded, 'headers.client_id');
        $domain = Arr::get($decoded, 'headers.domain');

        if ($clientId !== config('uspacy.uspacy_app_client_id')) {
            \abort(401, 'Invalid client ID');
        } elseif (! $domain) {
            \abort(401, 'Domain is required in token header');
        }

        $this->portalInstaller->install(
            $domain,
            $request->validated('token'),
            $request->validated('refresh_token'),
            $request->validated('expiry_date_token')
        );

        return response()->json(['message' => 'Portal installed successfully']);
    }

    public function uninstall(): JsonResponse
    {
        $token = request()->bearerToken();

        if (! $token) {
            \abort(401, 'Token is required');
        }

        $decoded = $this->jwtService->decode($token, config('uspacy.uspacy_app_client_secret'));
        $domain = Arr::get($decoded, 'headers.domain');

        if (! $domain) {
            \abort(401, 'Domain is required in token header');
        }

        $this->portalInstaller->uninstall($domain);

        return response()->json(['message' => 'Portal uninstalled successfully']);
    }
}
