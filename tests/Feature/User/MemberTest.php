<?php

namespace Tests\Feature\User;

use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spectator\Spectator;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/members';

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

    protected function fakeStorage(): void
    {
        Storage::fake('public');
    }

    // -------------------------------------------------------------------------
    // Index Members
    // -------------------------------------------------------------------------

    public function test_get_members_success(): void
    {
        $position = Position::factory()->create();
        Member::factory()->count(3)->create(['position_id' => $position->id]);

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_members_with_pagination(): void
    {
        $position = Position::factory()->create();
        Member::factory()->count(15)->create(['position_id' => $position->id]);

        $response = $this->withToken($this->token)->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_get_members_with_search(): void
    {
        $position = Position::factory()->create();
        Member::factory()->create(['name' => 'Ahmad Ikbal', 'position_id' => $position->id]);
        Member::factory()->create(['name' => 'Budi Santoso', 'position_id' => $position->id]);
        Member::factory()->create(['name' => 'Ahmad Rizky', 'position_id' => $position->id]);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=Ahmad');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_members_with_position_id_filter(): void
    {
        $position1 = Position::factory()->create();
        $position2 = Position::factory()->create();
        Member::factory()->count(2)->create(['position_id' => $position1->id]);
        Member::factory()->count(3)->create(['position_id' => $position2->id]);

        $response = $this->withToken($this->token)->get($this->base_url."?position_id={$position1->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_members_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    // -------------------------------------------------------------------------
    // Show Member
    // -------------------------------------------------------------------------

    public function test_show_member_success(): void
    {
        $position = Position::factory()->create();
        $member = Member::factory()->create(['position_id' => $position->id]);

        $response = $this->withToken($this->token)->get("{$this->base_url}/{$member->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.id', $member->id);
        $response->assertJsonPath('data.name', $member->name);
        $response->assertJsonPath('data.position.id', $position->id);
    }

    public function test_show_member_unauthenticated(): void
    {
        $member = Member::factory()->create();

        $response = $this->get("{$this->base_url}/{$member->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    public function test_show_member_not_found(): void
    {
        $response = $this->withToken($this->token)->get("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Store Member
    // -------------------------------------------------------------------------

    public function test_store_member_success(): void
    {
        $this->fakeStorage();

        $position = Position::factory()->create();

        $payload = [
            'name' => 'Ahmad Ikbal',
            'photo' => UploadedFile::fake()->image('member.jpg'),
            'position_id' => $position->id,
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(201);

        $response->assertJsonPath('data.name', $payload['name']);
        $response->assertJsonPath('data.position.id', $position->id);

        $this->assertDatabaseHas('members', [
            'name' => $payload['name'],
            'position_id' => $position->id,
        ]);
    }

    public function test_store_member_without_photo(): void
    {
        $position = Position::factory()->create();

        $payload = [
            'name' => 'Ahmad Ikbal',
            'position_id' => $position->id,
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(201);

        $response->assertJsonPath('data.name', $payload['name']);
    }

    public function test_store_member_unauthenticated(): void
    {
        $position = Position::factory()->create();

        $payload = [
            'name' => 'Ahmad Ikbal',
            'position_id' => $position->id,
        ];

        $response = $this->post($this->base_url, $payload);

        $response->assertValidResponse(401);
    }

    public function test_store_member_validation_fails_when_fields_missing(): void
    {
        $payload = [
            'name' => '',
            'position_id' => '',
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'name', 'position_id',
        ]);
    }

    public function test_store_member_validation_fails_when_position_id_invalid(): void
    {
        $payload = [
            'name' => 'Ahmad Ikbal',
            'position_id' => 999,
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['position_id']);
    }

    // -------------------------------------------------------------------------
    // Update Member
    // -------------------------------------------------------------------------

    public function test_update_member_success(): void
    {
        $position = Position::factory()->create();
        $member = Member::factory()->create(['position_id' => $position->id]);

        $newPosition = Position::factory()->create();

        $payload = [
            'name' => 'Updated Name',
            'photo' => null,
            'position_id' => $newPosition->id,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$member->id}", $payload);

        $response->assertValidResponse(200);

        $response->assertJsonPath('data.name', 'Updated Name');
        $response->assertJsonPath('data.position.id', $newPosition->id);

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Updated Name',
            'position_id' => $newPosition->id,
        ]);
    }

    public function test_update_member_with_photo(): void
    {
        $this->fakeStorage();

        $oldPhoto = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldPhoto->store('members');

        $position = Position::factory()->create();
        $member = Member::factory()->create([
            'photo' => $oldPath,
            'position_id' => $position->id,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'photo' => UploadedFile::fake()->image('new.jpg'),
            'position_id' => $position->id,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$member->id}", $payload);

        $response->assertValidResponse(200);

        Storage::assertMissing($oldPath);

        $member->refresh();
        Storage::assertExists($member->photo);
    }

    public function test_update_member_unauthenticated(): void
    {
        $member = Member::factory()->create();

        $payload = [
            'name' => 'Updated Name',
            'position_id' => $member->position_id,
        ];

        $response = $this->put("{$this->base_url}/{$member->id}", $payload);

        $response->assertValidResponse(401);
    }

    public function test_update_member_not_found(): void
    {
        $position = Position::factory()->create();

        $payload = [
            'name' => 'Updated Name',
            'position_id' => $position->id,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/999", $payload);

        $response->assertValidResponse(404);
    }

    public function test_update_member_validation_fails_when_fields_missing(): void
    {
        $member = Member::factory()->create();

        $payload = [
            'name' => '',
            'position_id' => '',
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$member->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'name', 'position_id',
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy Member
    // -------------------------------------------------------------------------

    public function test_destroy_member_success(): void
    {
        $this->fakeStorage();

        $photo = UploadedFile::fake()->image('member.jpg');
        $photoPath = $photo->store('members');

        $member = Member::factory()->create(['photo' => $photoPath]);

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$member->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        Storage::assertMissing($photoPath);
    }

    public function test_destroy_member_without_photo(): void
    {
        $member = Member::factory()->create(['photo' => null]);

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$member->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    }

    public function test_destroy_member_unauthenticated(): void
    {
        $member = Member::factory()->create();

        $response = $this->deleteJson("{$this->base_url}/{$member->id}");

        $response->assertValidResponse(401);
    }

    public function test_destroy_member_not_found(): void
    {
        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Bulk Destroy Member
    // -------------------------------------------------------------------------

    public function test_bulk_destroy_members_by_ids_success(): void
    {
        $members = Member::factory()->count(3)->create();
        $ids = $members->pluck('id')->toArray();

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/bulk-destroy", [
                'ids' => $ids,
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.deleted_count', 3);
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('members', ['id' => $id]);
        }
    }

    public function test_bulk_destroy_members_select_all_success(): void
    {
        Member::factory()->count(5)->create();
        $exclude_member = Member::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/bulk-destroy", [
                'select_all' => true,
                'exclude_ids' => [$exclude_member->id],
            ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.deleted_count', 5);
        $this->assertDatabaseHas('members', ['id' => $exclude_member->id]);
        $this->assertEquals(1, Member::count());
    }

    public function test_bulk_destroy_members_unauthenticated(): void
    {
        $response = $this->deleteJson("{$this->base_url}/bulk-destroy", [
            'ids' => [1, 2, 3],
        ]);

        $response->assertValidResponse(401);
    }
}
