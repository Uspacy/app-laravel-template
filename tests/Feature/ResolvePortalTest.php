<?php

namespace Tests\Feature;

use App\Http\Middleware\ResolvePortalMiddleware;
use App\Models\Portal;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ResolvePortalTest extends TestCase
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

        Route::middleware(ResolvePortalMiddleware::class)->get('/{integration}/_test/portal', fn (Request $request) => [
            'domain' => \portal()->domain,
            'integration_code' => \portal()->integration_code,
        ]);
    }

    public function test_it_sets_portal_context(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'other']);

        $this->withToken($this->makeToken('acme.uspacy.com'))
            ->getJson('/crm/_test/portal')
            ->assertOk()
            ->assertJson(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);
    }

    public function test_it_rejects_missing_token(): void
    {
        $this->getJson('/crm/_test/portal')->assertUnauthorized();
    }

    public function test_it_rejects_invalid_token(): void
    {
        $this->withToken('invalid')->getJson('/crm/_test/portal')->assertUnauthorized();
    }

    public function test_it_rejects_token_without_domain(): void
    {
        $this->withToken($this->makeToken(null))->getJson('/crm/_test/portal')->assertUnauthorized();
    }

    public function test_it_rejects_token_without_integration_code(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);

        $this->withToken($this->makeToken('acme.uspacy.com', null))
            ->getJson('/crm/_test/portal')
            ->assertUnauthorized();
    }

    public function test_it_rejects_not_installed_integration(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);

        $this->withToken($this->makeToken('acme.uspacy.com', 'other', self::OTHER_SECRET))
            ->getJson('/other/_test/portal')
            ->assertUnauthorized();
    }

    public function test_it_returns_not_found_for_unconfigured_pathname(): void
    {
        $this->withToken($this->makeToken('acme.uspacy.com'))
            ->getJson('/unknown/_test/portal')
            ->assertNotFound();
    }

    public function test_it_rejects_integration_code_not_matching_pathname(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'other']);

        $this->withToken($this->makeToken('acme.uspacy.com', 'other'))
            ->getJson('/crm/_test/portal')
            ->assertUnauthorized();
    }

    public function test_it_rejects_token_signed_with_another_integration_secret(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);

        $this->withToken($this->makeToken('acme.uspacy.com', 'crm', self::OTHER_SECRET))
            ->getJson('/crm/_test/portal')
            ->assertUnauthorized();
    }

    public function test_it_rejects_unknown_portal(): void
    {
        $this->withToken($this->makeToken('unknown.uspacy.com'))
            ->getJson('/crm/_test/portal')
            ->assertUnauthorized();
    }

    private function makeToken(?string $domain, ?string $integrationCode = 'crm', string $secret = self::SECRET): string
    {
        return JWT::encode(['iat' => time()], $secret, 'HS256', head: array_filter([
            'domain' => $domain,
            'integration_code' => $integrationCode,
        ]));
    }
}
