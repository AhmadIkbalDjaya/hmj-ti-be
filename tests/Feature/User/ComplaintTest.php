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
}
