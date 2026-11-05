<?php

namespace App\Factories;

use App\Models\Portal;
use Saloon\Http\Auth\TokenAuthenticator;
use Uspacy\SDK\Http\Client\Middlewares\RefreshApplicationTokenMiddleware;
use Uspacy\SDK\Http\Client\Requests\Auth\Tokens;
use Uspacy\SDK\Http\Client\UspacySDK;

class UspacySDKFactory
{
    public function fromPortal(Portal $portal): UspacySDK
    {
        [$clientId, $clientSecret] = $this->credentials($portal);
        $storeTokens = fn (Tokens $tokens) => $portal->update([
            'token' => $tokens->token,
            'refresh_token' => $tokens->refreshToken,
            'expiry_date' => now()->addSeconds((int) $tokens->expiresIn),
        ]);

        $sdk = new UspacySDK("https://{$portal->domain}", $portal->token);

        if ($portal->isTokenExpired()) {
            $tokens = $sdk->auth()->applicationSignIn($clientId, $clientSecret);
            $sdk->authenticate(new TokenAuthenticator($tokens->token));
            $storeTokens($tokens);
        }

        $sdk->middleware()->onResponse(new RefreshApplicationTokenMiddleware($clientId, $clientSecret, $storeTokens));

        return $sdk;
    }

    /**
     * @return array{string, string} [client id, client secret]
     */
    private function credentials(Portal $portal): array
    {
        $integration = collect(config('uspacy.integrations'))
            ->firstWhere('integration_code', $portal->integration_code);

        if (empty($integration['client_id']) || empty($integration['client_secret'])) {
            throw new \RuntimeException("Client ID and secret must be configured for integration: {$portal->integration_code}");
        }

        return [$integration['client_id'], $integration['client_secret']];
    }
}
