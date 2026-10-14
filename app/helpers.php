<?php

use App\Http\Middleware\ResolvePortalMiddleware;
use App\Models\Portal;

if (! function_exists('portal')) {
    function portal()
    {
        $portal = \request()->attributes->get(ResolvePortalMiddleware::PORTAL_ATTRIBUTE);

        if (! $portal instanceof Portal) {
            \abort(401, 'Application portal not installed');
        }

        return $portal;
    }
}
