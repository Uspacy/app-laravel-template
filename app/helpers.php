<?php

use App\Models\Portal;

if (! function_exists('portal')) {
    function portal()
    {
        $portal = request()->attributes->get('portal');

        if (! $portal instanceof Portal) {
            \abort(401, 'Application portal not installed');
        }

        return $portal;
    }
}
