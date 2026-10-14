<?php

namespace App\Models;

use App\Http\Resources\PortalResource;
use Database\Factories\PortalFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $domain
 * @property Carbon $expiry_date
 * @property string $token
 * @property string $refresh_token
 *
 * @method static \Database\Factories\PortalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portal whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[UseFactory(PortalFactory::class)]
#[UseResource(PortalResource::class)]
class Portal extends Model
{
    /** @use HasFactory<PortalFactory> */
    use HasFactory;

    protected $fillable = [
        'domain',
        'token',
        'refresh_token',
        'expiry_date',
    ];

    protected $hidden = [
        'token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expiry_date' => 'datetime',
        ];
    }
}
