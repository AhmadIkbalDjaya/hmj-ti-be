<?php

namespace Tests\Feature\User;

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/positions';

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
    // Index Positions
    // -------------------------------------------------------------------------

    public function test_get_positions_success(): void
    {
        Position::factory()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_positions_with_pagination(): void
    {
        Position::factory()->count(15)->create();

        $response = $this->withToken($this->token)->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_get_positions_with_search(): void
    {
        Position::factory()->create(['name' => 'Ketua Umum']);
        Position::factory()->create(['name' => 'Wakil Ketua']);
        Position::factory()->create(['name' => 'Sekretaris']);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=Ketua');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_positions_with_is_active_filter(): void
    {
        Position::factory()->count(2)->create(['is_active' => true]);
        Position::factory()->inactive()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url.'?is_active=1');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_positions_with_level_filter(): void
    {
        Position::factory()->count(2)->create(['level' => 1]);
        Position::factory()->count(3)->create(['level' => 2]);

        $response = $this->withToken($this->token)->get($this->base_url.'?level=1');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_positions_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    // -------------------------------------------------------------------------
    // Show Position
    // -------------------------------------------------------------------------

    public function test_show_position_success(): void
    {
        $position = Position::factory()->create();

        $response = $this->withToken($this->token)->get("{$this->base_url}/{$position->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.id', $position->id);
        $response->assertJsonPath('data.name', $position->name);
        $response->assertJsonPath('data.slug', $position->slug);
    }

    public function test_show_position_unauthenticated(): void
    {
        $position = Position::factory()->create();

        $response = $this->get("{$this->base_url}/{$position->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    public function test_show_position_not_found(): void
    {
        $response = $this->withToken($this->token)->get("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Store Position
    // -------------------------------------------------------------------------

    public function test_store_position_success(): void
    {
        $payload = [
            'name' => 'Ketua Umum',
            'slug' => 'ketua-umum',
            'parent_id' => null,
            'level' => 1,
            'order_index' => 1,
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->postJson($this->base_url, $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(201);

        $response->assertJsonPath('data.name', $payload['name']);
        $response->assertJsonPath('data.slug', $payload['slug']);

        $this->assertDatabaseHas('positions', [
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ]);
    }

    public function test_store_position_with_parent(): void
    {
        $parent = Position::factory()->create();

        $payload = [
            'name' => 'Wakil Ketua',
            'slug' => 'wakil-ketua',
            'parent_id' => $parent->id,
            'level' => 1,
            'order_index' => 2,
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->postJson($this->base_url, $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(201);

        $response->assertJsonPath('data.parent_id', $parent->id);
    }

    public function test_store_position_unauthenticated(): void
    {
        $payload = [
            'name' => 'Ketua Umum',
            'slug' => 'ketua-umum',
            'parent_id' => null,
            'level' => 1,
            'order_index' => 1,
            'is_active' => true,
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidResponse(401);
    }

    public function test_store_position_validation_fails_when_fields_missing(): void
    {
        $payload = [
            'name' => '',
            'slug' => '',
            'level' => '',
            'is_active' => '',
        ];

        $response = $this->withToken($this->token)
            ->postJson($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'name', 'slug', 'level', 'is_active',
        ]);
    }

    public function test_store_position_validation_fails_when_slug_is_duplicate(): void
    {
        Position::factory()->create(['slug' => 'existing-slug']);

        $payload = [
            'name' => 'Another Position',
            'slug' => 'existing-slug',
            'parent_id' => null,
            'level' => 1,
            'order_index' => 1,
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->postJson($this->base_url, $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['slug']);
    }

    // -------------------------------------------------------------------------
    // Update Position
    // -------------------------------------------------------------------------

    public function test_update_position_success(): void
    {
        $position = Position::factory()->create();

        $payload = [
            'name' => 'Updated Position',
            'slug' => 'updated-position',
            'level' => 2,
            'order_index' => 5,
            'is_active' => false,
        ];

        $response = $this->withToken($this->token)
            ->putJson("{$this->base_url}/{$position->id}", $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.name', 'Updated Position');
        $response->assertJsonPath('data.slug', 'updated-position');

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'name' => 'Updated Position',
            'slug' => 'updated-position',
        ]);
    }

    public function test_update_position_unauthenticated(): void
    {
        $position = Position::factory()->create();

        $payload = [
            'name' => 'Updated Position',
            'slug' => 'updated-position',
            'parent_id' => null,
            'level' => 2,
            'order_index' => 1,
            'is_active' => true,
        ];

        $response = $this->putJson("{$this->base_url}/{$position->id}", $payload);

        $response->assertValidResponse(401);
    }

    public function test_update_position_not_found(): void
    {
        $payload = [
            'name' => 'Updated Position',
            'slug' => 'updated-position',
            'parent_id' => null,
            'level' => 2,
            'order_index' => 1,
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->putJson("{$this->base_url}/999", $payload);

        $response->assertValidResponse(404);
    }

    public function test_update_position_validation_fails_when_fields_missing(): void
    {
        $position = Position::factory()->create();

        $payload = [
            'name' => '',
            'slug' => '',
            'level' => '',
            'is_active' => '',
        ];

        $response = $this->withToken($this->token)
            ->putJson("{$this->base_url}/{$position->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'name', 'slug', 'level', 'is_active',
        ]);
    }

    public function test_update_position_validation_fails_when_slug_is_duplicate(): void
    {
        Position::factory()->create(['slug' => 'taken-slug']);
        $position = Position::factory()->create();

        $payload = [
            'name' => 'Updated Position',
            'slug' => 'taken-slug',
            'parent_id' => null,
            'level' => 2,
            'order_index' => 1,
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->putJson("{$this->base_url}/{$position->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['slug']);
    }

    // -------------------------------------------------------------------------
    // Destroy Position
    // -------------------------------------------------------------------------

    public function test_destroy_position_success(): void
    {
        $position = Position::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$position->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    public function test_destroy_position_unauthenticated(): void
    {
        $position = Position::factory()->create();

        $response = $this->deleteJson("{$this->base_url}/{$position->id}");

        $response->assertValidResponse(401);
    }

    public function test_destroy_position_not_found(): void
    {
        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }
}
