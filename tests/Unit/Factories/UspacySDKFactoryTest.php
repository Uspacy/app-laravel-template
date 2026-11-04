<?php

namespace Tests\Unit\Factories;

use App\Factories\UspacySDKFactory;
use App\Models\Portal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

class UspacySDKFactoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['uspacy.integrations' => [
            'crm' => ['integration_code' => 'crm', 'client_id' => 'crm-client', 'client_secret' => 'crm-secret'],
        ]]);
    }

    public function test_it_refreshes_and_stores_tokens_on_unauthorized_response(): void
    {
        $this->freezeSecond();
        $portal = Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm', 'token' => 'stale']);
        $sdk = (new UspacySDKFactory)->fromPortal($portal);
        $sdk->withMockClient(new MockClient([
            MockResponse::make([], 401),
            MockResponse::make(['jwt' => 'fresh', 'refreshToken' => 'fresh-refresh', 'expireInSeconds' => 3600]),
            MockResponse::make([]),
        ]));

        $sdk->http()->get('/some/endpoint');

        $portal->refresh();
        $this->assertSame('fresh', $portal->token);
        $this->assertSame('fresh-refresh', $portal->refresh_token);
        $this->assertTrue($portal->expiry_date->equalTo(now()->addHour()));
    }

    public function test_it_requires_integration_credentials(): void
    {
        $portal = Portal::factory()->make(['integration_code' => 'unknown']);

        $this->expectException(\RuntimeException::class);

        (new UspacySDKFactory)->fromPortal($portal);
    }
}
