<?php

namespace App\Services\Portal;

use App\Models\Portal;

class PortalInstaller
{
    public function install(
        string $domain,
        string $token,
        string $refreshToken,
        string $expiryDate
    ) {
        return Portal::updateOrCreate(
            [
                'domain' => $domain,
            ],
            [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'expiry_date_token' => $expiryDate,
            ]
        );
    }

    public function uninstall(string $domain)
    {
        return Portal::where('domain', $domain)->delete();
    }
}
