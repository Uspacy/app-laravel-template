<?php

namespace Tests\Feature\V1;

use App\Models\Portal;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalInstallTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret-test-secret-test-secret';

    private const CLIENT_ID = 'test-client';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'uspacy.uspacy_app_client_id' => self::CLIENT_ID,
            'uspacy.uspacy_app_client_secret' => self::SECRET,
        ]);
    }

    public function test_it_installs_portal(): void
    {
        $expiryDate = now()->addHour()->startOfSecond();

        $this->withToken($this->makeToken('acme.uspacy.com'))
            ->postJson('/v1/portals/install', [
                'token' => 'access',
                'refresh_token' => 'refresh',
                'expiry_date' => $expiryDate->getTimestamp(),
            ])
            ->assertOk();

        $portal = Portal::where('domain', 'acme.uspacy.com')->sole();
        $this->assertSame('access', $portal->token);
        $this->assertSame('refresh', $portal->refresh_token);
        $this->assertTrue($portal->expiry_date->equalTo($expiryDate));
    }

    public function test_it_uninstalls_portal(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com']);

        $this->withToken($this->makeToken('acme.uspacy.com'))
            ->deleteJson('/v1/portals/uninstall')
            ->assertOk();

        $this->assertDatabaseMissing('portals', ['domain' => 'acme.uspacy.com']);
    }

    private function makeToken(string $domain): string
    {
        return JWT::encode(['iat' => time()], self::SECRET, 'HS256', head: [
            'client_id' => self::CLIENT_ID,
            'domain' => $domain,
        ]);
    }
}
