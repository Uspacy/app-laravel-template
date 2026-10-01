<?php

use App\Services\Integration\Context\PortalContext;

if (! function_exists('integration')) {
    function integration()
    {
        return app(PortalContext::class)->getIntegrationOrFail();
    }
}
