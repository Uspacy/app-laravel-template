<?php

namespace App\Services\Portal\Context;

use App\Models\Portal;

abstract class PortalContext
{
    abstract public function getPortal(): ?Portal;

    public function getPortalOrFail()
    {
        $portal = $this->getPortal();

        if (! $portal) {
            \abort(401, 'Unauthenticated.');
        }

        return $portal;
    }
}
