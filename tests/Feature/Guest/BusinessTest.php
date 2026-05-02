<?php

namespace Tests\Feature\Guest;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/businesses';

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    // -------------------------------------------------------------------------
    // Index Business (Public)
    // -------------------------------------------------------------------------

    public function test_get_business_success(): void
    {
        Business::factory()->count(3)->create(['is_active' => true]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_business_only_returns_active(): void
    {
        Business::factory()->count(2)->create(['is_active' => true]);
        Business::factory()->inactive()->count(3)->create();

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_business_with_pagination(): void
    {
        Business::factory()->count(15)->create(['is_active' => true]);

        $response = $this->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_get_business_returns_correct_structure(): void
    {
        Business::factory()->create(['is_active' => true]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => ['id', 'slug', 'title', 'description', 'price', 'image', 'whatsapp', 'is_active'],
            ],
            'meta' => ['page', 'limit', 'total', 'total_page'],
        ]);
    }

    public function test_get_business_empty_when_no_active(): void
    {
        Business::factory()->inactive()->count(3)->create();

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(0, 'data');
    }
}
