<?php

namespace App\Support;

use App\Models\Portal;
use Illuminate\Container\Attributes\Scoped;

#[Scoped]
class PortalContext
{
    private ?Portal $portal = null;

    public function set(Portal $portal): void
    {
        $this->portal = $portal;
    }

    public function get(): Portal
    {
        return $this->portal ?? \abort(401, 'Portal context is not set');
    }
}
