<?php

namespace Tests\Feature\User;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/complaints';

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
    // Index Complaints
    // -------------------------------------------------------------------------

    public function test_get_complaints_success(): void
    {
        Complaint::factory()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_complaints_with_pagination(): void
    {
        Complaint::factory()->count(15)->create();

        $response = $this->withToken($this->token)->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_get_complaints_with_search(): void
    {
        Complaint::factory()->create(['name' => 'Ahmad Ikbal']);
        Complaint::factory()->create(['name' => 'John Doe', 'description' => 'Report from Ahmad']);
        Complaint::factory()->create(['name' => 'Jane Doe']);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=Ahmad');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_complaints_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    // -------------------------------------------------------------------------
    // Show Complaint
    // -------------------------------------------------------------------------

    public function test_show_complaint_success(): void
    {
        $complaint = Complaint::factory()->create();

        $response = $this->withToken($this->token)->get("{$this->base_url}/{$complaint->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.id', $complaint->id);
        $response->assertJsonPath('data.name', $complaint->name);
        $response->assertJsonPath('data.description', $complaint->description);
    }

    public function test_show_complaint_unauthenticated(): void
    {
        $complaint = Complaint::factory()->create();

        $response = $this->get("{$this->base_url}/{$complaint->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    public function test_show_complaint_not_found(): void
    {
        $response = $this->withToken($this->token)->get("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Destroy Complaint
    // -------------------------------------------------------------------------

    public function test_destroy_complaint_success(): void
    {
        $complaint = Complaint::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$complaint->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('complaints', ['id' => $complaint->id]);
    }

    public function test_destroy_complaint_unauthenticated(): void
    {
        $complaint = Complaint::factory()->create();

        $response = $this->deleteJson("{$this->base_url}/{$complaint->id}");

        $response->assertValidResponse(401);
    }

    public function test_destroy_complaint_not_found(): void
    {
        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Bulk Destroy Complaint
    // -------------------------------------------------------------------------

    public function test_bulk_destroy_complaints_by_ids_success(): void
    {
        $complaints = Complaint::factory()->count(3)->create();
        $ids = $complaints->pluck('id')->toArray();

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/bulk-destroy", [
                'ids' => $ids,
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.deleted_count', 3);
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('complaints', ['id' => $id]);
        }
    }

    public function test_bulk_destroy_complaints_select_all_success(): void
    {
        Complaint::factory()->count(5)->create();
        $exclude_complaint = Complaint::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/bulk-destroy", [
                'select_all' => true,
                'exclude_ids' => [$exclude_complaint->id],
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.deleted_count', 5);
        $this->assertDatabaseHas('complaints', ['id' => $exclude_complaint->id]);
        $this->assertEquals(1, Complaint::count());
    }

    public function test_bulk_destroy_complaints_unauthenticated(): void
    {
        $response = $this->deleteJson("{$this->base_url}/bulk-destroy", [
            'ids' => [1, 2, 3],
        ]);

        $response->assertValidResponse(401);
    }

    // -------------------------------------------------------------------------
    // Toggle Read Complaint
    // -------------------------------------------------------------------------

    public function test_toggle_read_complaint_success(): void
    {
        $complaint = Complaint::factory()->create(['is_read' => false, 'read_at' => null]);

        $response = $this->withToken($this->token)
            ->patchJson("{$this->base_url}/{$complaint->id}/toggle-read", [
                'is_read' => true,
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertTrue($complaint->fresh()->is_read);
        $this->assertNotNull($complaint->fresh()->read_at);

        // Toggle back
        $response = $this->withToken($this->token)
            ->patchJson("{$this->base_url}/{$complaint->id}/toggle-read", [
                'is_read' => false,
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertFalse($complaint->fresh()->is_read);
        $this->assertNull($complaint->fresh()->read_at);
    }

    public function test_toggle_read_complaint_validation_error(): void
    {
        $complaint = Complaint::factory()->create();

        $response = $this->withToken($this->token)
            ->patchJson("{$this->base_url}/{$complaint->id}/toggle-read", [
                'is_read' => 'not-a-boolean',
            ]);

        $response->assertValidResponse(422);
    }

    public function test_toggle_read_complaint_unauthenticated(): void
    {
        $complaint = Complaint::factory()->create();

        $response = $this->patchJson("{$this->base_url}/{$complaint->id}/toggle-read", [
            'is_read' => true,
        ]);

        $response->assertValidResponse(401);
    }

    public function test_toggle_read_complaint_not_found(): void
    {
        $response = $this->withToken($this->token)
            ->patchJson("{$this->base_url}/999/toggle-read", [
                'is_read' => true,
            ]);

        $response->assertValidResponse(404);
    }
}
