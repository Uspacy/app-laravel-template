<?php

namespace Tests\Feature;

use App\Models\Portal;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalInstallTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret-test-secret-test-secret';

    private const OTHER_SECRET = 'other-secret-other-secret-other-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['uspacy.integrations' => [
            'crm' => ['integration_code' => 'crm', 'client_id' => 'crm-client', 'client_secret' => self::SECRET],
            'other' => ['integration_code' => 'other', 'client_id' => 'other-client', 'client_secret' => self::OTHER_SECRET],
        ]]);
    }

    public function test_it_installs_portal(): void
    {
        $expiryDate = now()->addHour()->startOfSecond();

        $this->withToken($this->makeToken('acme.uspacy.com'))
            ->postJson('/crm/v1/portals/install', [
                'token' => 'access',
                'refresh_token' => 'refresh',
                'expiry_date' => $expiryDate->getTimestamp(),
            ])
            ->assertOk();

        $portal = Portal::where('domain', 'acme.uspacy.com')->where('integration_code', 'crm')->sole();
        $this->assertSame('access', $portal->token);
        $this->assertSame('refresh', $portal->refresh_token);
        $this->assertTrue($portal->expiry_date->equalTo($expiryDate));
    }

    public function test_it_installs_multiple_integrations_on_same_domain(): void
    {
        foreach (['crm', 'other'] as $integrationCode) {
            $this->withToken($this->makeToken('acme.uspacy.com', $integrationCode))
                ->postJson("/{$integrationCode}/v1/portals/install", [
                    'token' => "access-{$integrationCode}",
                    'refresh_token' => 'refresh',
                    'expiry_date' => now()->addHour()->getTimestamp(),
                ])
                ->assertOk();
        }

        $this->assertSame(2, Portal::where('domain', 'acme.uspacy.com')->count());
    }

    public function test_it_rejects_install_with_foreign_client_id(): void
    {
        $this->withToken($this->makeToken('acme.uspacy.com', 'crm', clientId: 'other-client'))
            ->postJson('/crm/v1/portals/install', [
                'token' => 'access',
                'refresh_token' => 'refresh',
                'expiry_date' => now()->addHour()->getTimestamp(),
            ])
            ->assertUnauthorized();
    }

    public function test_it_rejects_install_without_integration_code(): void
    {
        $this->withToken($this->makeToken('acme.uspacy.com', null))
            ->postJson('/crm/v1/portals/install', [
                'token' => 'access',
                'refresh_token' => 'refresh',
                'expiry_date' => now()->addHour()->getTimestamp(),
            ])
            ->assertUnauthorized();
    }

    public function test_it_uninstalls_only_matching_integration(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'other']);

        $this->withToken($this->makeToken('acme.uspacy.com'))
            ->deleteJson('/crm/v1/portals/uninstall')
            ->assertOk();

        $this->assertDatabaseMissing('portals', ['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);
        $this->assertDatabaseHas('portals', ['domain' => 'acme.uspacy.com', 'integration_code' => 'other']);
    }

    private function makeToken(string $domain, ?string $integrationCode = 'crm', ?string $clientId = null): string
    {
        $credentials = config('uspacy.integrations')[$integrationCode] ?? ['client_id' => null, 'client_secret' => self::SECRET];

        return JWT::encode(['iat' => time()], $credentials['client_secret'], 'HS256', head: array_filter([
            'client_id' => $clientId ?? $credentials['client_id'],
            'domain' => $domain,
            'integration_code' => $integrationCode,
        ]));
    }
}
