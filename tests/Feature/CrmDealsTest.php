<?php

namespace Tests\Feature;

use App\Models\Portal;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

class CrmDealsTest extends TestCase
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

    protected function tearDown(): void
    {
        MockClient::destroyGlobal();

        parent::tearDown();
    }

    public function test_it_returns_deals_from_uspacy(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);
        $mock = MockClient::global([
            MockResponse::make(['data' => [['id' => 1, 'title' => 'Deal']], 'meta' => ['total' => 1, 'page' => 1, 'list' => 10]]),
        ]);
        $token = JWT::encode(['iat' => time(), 'domain' => 'acme.uspacy.com', 'integration_code' => 'crm'], self::SECRET, 'HS256');

        $this->withToken($token)
            ->getJson('/crm/crm/deals')
            ->assertOk()
            ->assertExactJson([
                'data' => [['id' => 1, 'title' => 'Deal']],
                'meta' => ['total' => 1, 'page' => 1, 'list' => 10],
            ]);

        $this->assertStringEndsWith('/crm/v1/entities/deals', $mock->getLastPendingRequest()->getUrl());
    }

    public function test_it_rejects_missing_token(): void
    {
        $this->getJson('/crm/crm/deals')->assertUnauthorized();
    }
}
