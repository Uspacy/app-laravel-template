<?php

namespace App\Services\Portal\Context;

use App\Models\Portal;

class StaticPortalContext extends PortalContext
{
    public function __construct(protected Portal $portal) {}

    public function getPortal(): Portal
    {
        return $this->portal;
    }
}
