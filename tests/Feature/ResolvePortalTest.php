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

    protected function setUp(): void
    {
        parent::setUp();

        config(['uspacy.uspacy_app_client_secret' => self::SECRET]);

        Route::middleware(ResolvePortalMiddleware::class)->get('/_test/portal', fn (Request $request) => [
            'domain' => \portal()->domain,
        ]);
    }

    public function test_it_adds_portal_to_request_attributes(): void
    {
        Portal::factory()->create(['domain' => 'acme.uspacy.com']);

        $this->withToken($this->makeToken('acme.uspacy.com'))
            ->getJson('/_test/portal')
            ->assertOk()
            ->assertJson(['domain' => 'acme.uspacy.com']);
    }

    public function test_it_rejects_missing_token(): void
    {
        $this->getJson('/_test/portal')->assertUnauthorized();
    }

    public function test_it_rejects_invalid_token(): void
    {
        $this->withToken('invalid')->getJson('/_test/portal')->assertUnauthorized();
    }

    public function test_it_rejects_token_without_domain(): void
    {
        $this->withToken($this->makeToken(null))->getJson('/_test/portal')->assertUnauthorized();
    }

    public function test_it_rejects_unknown_portal(): void
    {
        $this->withToken($this->makeToken('unknown.uspacy.com'))
            ->getJson('/_test/portal')
            ->assertUnauthorized();
    }

    private function makeToken(?string $domain): string
    {
        $claims = ['iat' => time()] + ($domain ? ['domain' => $domain] : []);

        return JWT::encode($claims, self::SECRET, 'HS256');
    }
}
