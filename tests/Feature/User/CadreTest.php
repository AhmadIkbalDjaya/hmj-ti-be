<?php

namespace Tests\Feature\User;

use App\Enums\CadreStatus;
use App\Models\Cadre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class CadreTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/cadres';

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    // -------------------------------------------------------------------------
    // Index Cadres
    // -------------------------------------------------------------------------

    public function test_get_cadres_success(): void
    {
        Cadre::factory()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_cadres_with_filters(): void
    {
        Cadre::factory()->create(['name' => 'John Doe', 'batch' => '2020', 'status' => CadreStatus::ACTIVE]);
        Cadre::factory()->create(['name' => 'Jane Doe', 'batch' => '2021', 'status' => CadreStatus::GRADUATED]);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=John&batch=2020&status=active');

        $response->assertValidRequest();
        $response->assertValidResponse(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'John Doe');
    }

    public function test_get_cadres_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidResponse(401);
    }

    // -------------------------------------------------------------------------
    // Store Cadre
    // -------------------------------------------------------------------------

    public function test_store_cadre_success(): void
    {
        $payload = [
            'name' => 'New Cadre',
            'address' => 'Some Address',
            'batch' => '2022',
            'status' => 'active',
        ];

        $response = $this->withToken($this->token)->postJson($this->base_url, $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(201);

        $this->assertDatabaseHas('cadres', $payload);
    }

    public function test_store_cadre_unauthenticated(): void
    {
        $payload = [
            'name' => 'New Cadre',
            'address' => 'Some Address',
            'batch' => '2022',
            'status' => 'active',
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidResponse(401);
    }

    public function test_store_cadre_validation_error(): void
    {
        $payload = [
            'name' => '', // Required
            'status' => 'invalid-status', // Enum
        ];

        $response = $this->withToken($this->token)->postJson($this->base_url, $payload);

        $response->assertValidResponse(422);
    }

    // -------------------------------------------------------------------------
    // Show Cadre
    // -------------------------------------------------------------------------

    public function test_show_cadre_success(): void
    {
        $cadre = Cadre::factory()->create();

        $response = $this->withToken($this->token)->get("{$this->base_url}/{$cadre->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);
        $response->assertJsonPath('data.id', $cadre->id);
    }

    public function test_show_cadre_unauthenticated(): void
    {
        $cadre = Cadre::factory()->create();

        $response = $this->get("{$this->base_url}/{$cadre->id}");

        $response->assertValidResponse(401);
    }

    public function test_show_cadre_not_found(): void
    {
        $response = $this->withToken($this->token)->get("{$this->base_url}/999");

        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Update Cadre
    // -------------------------------------------------------------------------

    public function test_update_cadre_success(): void
    {
        $cadre = Cadre::factory()->create();
        $payload = [
            'name' => 'Updated Name',
            'address' => 'Updated Address',
            'batch' => '2023',
            'status' => 'graduated',
        ];

        $response = $this->withToken($this->token)->putJson("{$this->base_url}/{$cadre->id}", $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseHas('cadres', array_merge(['id' => $cadre->id], $payload));
    }

    public function test_update_cadre_unauthenticated(): void
    {
        $cadre = Cadre::factory()->create();
        $payload = [
            'name' => 'Updated Name',
            'batch' => '2023',
            'status' => 'graduated',
        ];

        $response = $this->putJson("{$this->base_url}/{$cadre->id}", $payload);

        $response->assertValidResponse(401);
    }

    public function test_update_cadre_validation_error(): void
    {
        $cadre = Cadre::factory()->create();
        $payload = [
            'name' => '', // Required
            'status' => 'invalid-status', // Enum
        ];

        $response = $this->withToken($this->token)->putJson("{$this->base_url}/{$cadre->id}", $payload);

        $response->assertValidResponse(422);
    }

    public function test_update_cadre_not_found(): void
    {
        $payload = [
            'name' => 'Updated Name',
            'batch' => '2023',
            'status' => 'graduated',
        ];

        $response = $this->withToken($this->token)->putJson("{$this->base_url}/999", $payload);

        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Destroy Cadre
    // -------------------------------------------------------------------------

    public function test_destroy_cadre_success(): void
    {
        $cadre = Cadre::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("{$this->base_url}/{$cadre->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('cadres', ['id' => $cadre->id]);
    }

    public function test_destroy_cadre_unauthenticated(): void
    {
        $cadre = Cadre::factory()->create();

        $response = $this->deleteJson("{$this->base_url}/{$cadre->id}");

        $response->assertValidResponse(401);
    }

    public function test_destroy_cadre_not_found(): void
    {
        $response = $this->withToken($this->token)->deleteJson("{$this->base_url}/999");

        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Bulk Destroy Cadre
    // -------------------------------------------------------------------------

    public function test_bulk_destroy_cadres_by_ids_success(): void
    {
        $cadres = Cadre::factory()->count(3)->create();
        $ids = $cadres->pluck('id')->toArray();

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/bulk-destroy", [
                'ids' => $ids,
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('cadres', ['id' => $id]);
        }
    }

    public function test_bulk_destroy_cadres_select_all_success(): void
    {
        Cadre::factory()->count(5)->create(['status' => CadreStatus::ACTIVE]);
        $exclude_cadre = Cadre::factory()->create(['status' => CadreStatus::ACTIVE]);

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/bulk-destroy", [
                'select_all' => true,
                'exclude_ids' => [$exclude_cadre->id],
                'filters' => [
                    'status' => 'active',
                ],
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.deleted_count', 5);
        $this->assertDatabaseHas('cadres', ['id' => $exclude_cadre->id]);
        $this->assertEquals(1, Cadre::count());
    }

    public function test_bulk_destroy_cadres_unauthenticated(): void
    {
        $response = $this->deleteJson("{$this->base_url}/bulk-destroy", [
            'ids' => [1, 2, 3],
        ]);

        $response->assertValidResponse(401);
    }
}
