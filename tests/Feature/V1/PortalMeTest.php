<?php

namespace Tests\Feature\V1;

use App\Models\Portal;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalMeTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret-test-secret-test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['uspacy.integrations' => [
            'crm' => ['integration_code' => 'crm', 'client_id' => 'crm-client', 'client_secret' => self::SECRET],
        ]]);
    }

    public function test_it_returns_current_portal(): void
    {
        $portal = Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);
        $token = JWT::encode(['iat' => time(), 'domain' => 'acme.uspacy.com', 'integration_code' => 'crm'], self::SECRET, 'HS256');

        $this->withToken($token)
            ->getJson('/crm/v1/portals/me')
            ->assertOk()
            ->assertExactJson(['data' => [
                'id' => $portal->id,
                'domain' => 'acme.uspacy.com',
                'integration_code' => 'crm',
                'created_at' => $portal->created_at->toJSON(),
                'updated_at' => $portal->updated_at->toJSON(),
            ]]);
    }

    public function test_it_rejects_missing_token(): void
    {
        $this->getJson('/crm/v1/portals/me')->assertUnauthorized();
    }
}
