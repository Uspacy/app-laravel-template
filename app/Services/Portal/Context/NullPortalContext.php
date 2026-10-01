<?php

namespace App\Services\Portal\Context;

class NullPortalContext extends PortalContext
{
    public function getPortal(): null
    {
        return null;
    }
}
