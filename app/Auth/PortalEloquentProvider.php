<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class PortalEloquentProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        $domain = $credentials['domain'] ?? null;

        if (! $domain) {
            return null;
        }

        return $this->newModelQuery()
            ->where('domain', $domain)
            ->first();
    }
}
