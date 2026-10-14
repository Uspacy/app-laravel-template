<?php

namespace Tests\Unit\Models;

use App\Models\Portal;
use Tests\TestCase;

class PortalTest extends TestCase
{
    public function test_tokens_are_hidden(): void
    {
        $this->assertContains('token', (new Portal)->getHidden());
        $this->assertContains('refresh_token', (new Portal)->getHidden());
    }

    public function test_tokens_are_not_serialized(): void
    {
        $portal = Portal::factory()->make([
            'token' => 'secret-token',
            'refresh_token' => 'secret-refresh-token',
        ]);

        $array = $portal->toArray();
        $json = $portal->toJson();

        $this->assertArrayNotHasKey('token', $array);
        $this->assertArrayNotHasKey('refresh_token', $array);
        $this->assertArrayHasKey('domain', $array);
        $this->assertStringNotContainsString('secret-token', $json);
        $this->assertStringNotContainsString('secret-refresh-token', $json);
    }
}
