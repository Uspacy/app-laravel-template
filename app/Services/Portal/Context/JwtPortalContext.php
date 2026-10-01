<?php

namespace App\Services\Portal\Context;

use App\Models\Portal;
use Illuminate\Support\Facades\Auth;

class JwtPortalContext extends PortalContext
{
    public function getPortal(): ?Portal
    {
        $portal = Auth::guard('portals')->user();

        if ($portal && ! $portal instanceof Portal) {
            \abort(401, 'Unauthenticated.');
        }

        return $portal;
    }
}
