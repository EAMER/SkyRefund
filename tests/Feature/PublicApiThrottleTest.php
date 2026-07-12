<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicApiThrottleTest extends TestCase
{
    public function test_public_api_returns_429_after_many_requests(): void
    {
        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson('/api/v1/health');
        }

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(429);
    }
}
