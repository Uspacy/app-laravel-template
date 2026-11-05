<?php

use App\Models\Portal;
use App\Support\PortalContext;

if (! function_exists('portal')) {
    function portal(): Portal
    {
        return \app(PortalContext::class)->get();
    }
}
