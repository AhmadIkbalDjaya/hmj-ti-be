<?php

namespace Tests\Feature\Guest;

use App\Enums\CadreStatus;
use App\Models\Cadre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class CadreTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/cadres';

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    // -------------------------------------------------------------------------
    // Index Cadres (Public)
    // -------------------------------------------------------------------------

    public function test_get_cadres_success(): void
    {
        Cadre::factory()->count(3)->create();

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_cadres_with_filters(): void
    {
        Cadre::factory()->create(['name' => 'John Doe', 'batch' => '2020', 'status' => CadreStatus::ACTIVE]);
        Cadre::factory()->create(['name' => 'Jane Doe', 'batch' => '2021', 'status' => CadreStatus::GRADUATED]);

        $response = $this->get($this->base_url.'?search=John&batch=2020&status=active');

        $response->assertValidRequest();
        $response->assertValidResponse(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'John Doe');
    }

    public function test_get_cadres_with_pagination(): void
    {
        Cadre::factory()->count(15)->create();

        $response = $this->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }
}
