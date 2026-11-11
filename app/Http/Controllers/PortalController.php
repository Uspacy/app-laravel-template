<?php

namespace App\Http\Controllers;

use App\Http\Requests\Portals\InstallPortalRequest;
use App\Services\IntegrationTokenDecoder;
use App\Services\PortalInstaller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class PortalController extends Controller
{
    public function __construct(
        private PortalInstaller $portalInstaller,
        private IntegrationTokenDecoder $integrationTokenDecoder
    ) {}

    public function install(InstallPortalRequest $request): JsonResponse
    {
        [$domain, $integrationCode] = $this->resolveInstallationTarget($request);

        $this->portalInstaller->install(
            $domain,
            $integrationCode,
            $request->validated('token'),
            $request->validated('refresh_token'),
            $request->integer('expiry_date')
        );

        return response()->json(['message' => 'Portal installed successfully']);
    }

    public function uninstall(Request $request): JsonResponse
    {
        [$domain, $integrationCode] = $this->resolveInstallationTarget($request);

        $this->portalInstaller->uninstall($domain, $integrationCode);

        return response()->json(['message' => 'Portal uninstalled successfully']);
    }

    public function me(): JsonResource
    {
        return \portal()->toResource();
    }

    /**
     * @return array{string, string} [domain, integration code]
     */
    private function resolveInstallationTarget(Request $request): array
    {
        $token = $request->bearerToken();

        if (! $token) {
            \abort(401, 'Token is required');
        }

        $decoded = $this->integrationTokenDecoder->decode($token, $request->route('integration'), 'headers.integration_code');
        $domain = Arr::get($decoded, 'headers.domain');

        if (Arr::get($decoded, 'headers.client_id') !== $decoded['client_id']) {
            \abort(401, 'Invalid client ID');
        } elseif (! $domain) {
            \abort(401, 'Domain is required in token header');
        }

        return [$domain, $decoded['integration_code']];
    }
}
