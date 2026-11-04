<?php

namespace App\Services;

use Illuminate\Support\Arr;

class IntegrationTokenDecoder
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    /**
     * @param  string  $integrationCodePath  e.g. "headers.integration_code" or "claims.integration_code"
     * @return array{headers: array, claims: array, integration_code: string, client_id: string}
     */
    public function decode(string $token, string $pathname, string $integrationCodePath): array
    {
        $integration = config('uspacy.integrations')[$pathname] ?? null;

        if (! $integration) {
            \abort(404, 'Integration not found');
        }

        $decoded = $this->jwtService->decode($token, $integration['client_secret']);

        if (Arr::get($decoded, $integrationCodePath) !== $integration['integration_code']) {
            \abort(401, 'Integration code does not match');
        }

        return $decoded + [
            'integration_code' => $integration['integration_code'],
            'client_id' => $integration['client_id'],
        ];
    }
}
