<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_response(): void
    {
        $response = $this->get('/dashbpard');

        $response->assertStatus(200);
    }
}
