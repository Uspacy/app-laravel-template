<?php

namespace App\Models;

use Database\Factories\PortalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['domain', 'token', 'refresh_token', 'expiry_date'])]
#[Hidden(['token', 'refresh_token'])]
#[UseFactory(PortalFactory::class)]
class Portal extends Model
{
    /** @use HasFactory<PortalFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expiry_date' => 'datetime',
        ];
    }
}
