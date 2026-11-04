<?php

namespace App\Factories;

use App\Models\Portal;
use Uspacy\SDK\Http\Client\Middlewares\RefreshApplicationTokenMiddleware;
use Uspacy\SDK\Http\Client\Requests\Auth\Tokens;
use Uspacy\SDK\Http\Client\UspacySDK;

class UspacySDKFactory
{
    public function fromPortal(Portal $portal): UspacySDK
    {
        $sdk = new UspacySDK("https://{$portal->domain}", $portal->token);

        $this->initRefreshTokenMiddleware($sdk, $portal);

        return $sdk;
    }

    public function initRefreshTokenMiddleware(UspacySDK $sdk, Portal $portal): void
    {
        $integration = collect(config('uspacy.integrations'))
            ->firstWhere('integration_code', $portal->integration_code);

        if (empty($integration['client_id']) || empty($integration['client_secret'])) {
            throw new \RuntimeException("Client ID and secret must be configured for integration: {$portal->integration_code}");
        }

        $middleware = new RefreshApplicationTokenMiddleware(
            $integration['client_id'],
            $integration['client_secret'],
            function (Tokens $tokens) use ($portal) {
                $portal->update([
                    'token' => $tokens->token,
                    'refresh_token' => $tokens->refreshToken,
                    'expiry_date' => now()->addSeconds((int) $tokens->expiresIn),
                ]);
            }
        );

        $sdk->middleware()->onResponse($middleware);
    }
}
