<?php

namespace App\Services;

use App\Models\Portal;

class PortalInstaller
{
    public function install(
        string $domain,
        string $integrationCode,
        string $token,
        string $refreshToken,
        int $expiryDate
    ) {
        return Portal::updateOrCreate(
            [
                'domain' => $domain,
                'integration_code' => $integrationCode,
            ],
            [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'expiry_date' => $expiryDate,
            ]
        );
    }

    public function uninstall(string $domain, string $integrationCode)
    {
        return Portal::where('domain', $domain)
            ->where('integration_code', $integrationCode)
            ->delete();
    }
}
