<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class UspacyWebhooksTest extends TestCase
{
    public function test_it_logs_sns_message(): void
    {
        Log::spy();
        $message = ['Type' => 'Notification', 'MessageId' => 'id-1', 'Message' => '{"chatId":"42"}'];

        $this->call('POST', '/example/uspacy/webhooks', server: ['CONTENT_TYPE' => 'text/plain'], content: json_encode($message))
            ->assertOk();

        Log::shouldHaveReceived('info')->with('SNS message received', $message)->once();
    }

    public function test_it_rejects_invalid_json(): void
    {
        $this->call('POST', '/example/uspacy/webhooks', server: ['CONTENT_TYPE' => 'text/plain'], content: 'not json')
            ->assertBadRequest();
    }
}
