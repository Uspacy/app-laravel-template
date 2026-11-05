<?php

namespace Tests\Feature;

use App\Models\Portal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UspacyWebhooksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['uspacy.integrations' => [
            'example-integration' => ['integration_code' => 'crm', 'client_id' => 'crm-client', 'client_secret' => 'secret'],
        ]]);
    }

    public function test_it_logs_sns_message(): void
    {
        Log::spy();
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'crm']);
        $message = ['Type' => 'Notification', 'MessageId' => 'id-1', 'data' => ['domain' => 'acme.uspacy.com']];

        $this->postWebhook('example-integration', json_encode($message))->assertOk();

        Log::shouldHaveReceived('info')->with('SNS message received', $message)->once();
    }

    public function test_it_acknowledges_message_for_unknown_portal(): void
    {
        Log::spy();
        Portal::factory()->create(['domain' => 'acme.uspacy.com', 'integration_code' => 'other']);

        $message = ['data' => ['domain' => 'acme.uspacy.com']];

        $this->postWebhook('example-integration', json_encode($message))->assertOk();

        Log::shouldHaveReceived('warning')->with('Portal not found for SNS message', ['domain' => 'acme.uspacy.com'])->once();
        Log::shouldNotHaveReceived('info', ['SNS message received', $message]);
    }

    public function test_it_acknowledges_invalid_json(): void
    {
        $this->postWebhook('example-integration', 'not json')->assertOk();
    }

    public function test_it_returns_not_found_for_unconfigured_pathname(): void
    {
        $this->postWebhook('unknown', '{}')->assertNotFound();
    }

    private function postWebhook(string $integration, string $content): TestResponse
    {
        // SNS sends JSON with Content-Type: text/plain
        return $this->call('POST', "/{$integration}/v1/uspacy/webhooks", server: ['CONTENT_TYPE' => 'text/plain'], content: $content);
    }
}
