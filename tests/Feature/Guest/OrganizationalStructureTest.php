<?php

namespace Tests\Feature\Guest;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spectator\Spectator;
use Tests\TestCase;

class OrganizationalStructureTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/organizational-structure';

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
        Cache::forget(Position::ORGANIZATIONAL_STRUCTURE_CACHE_KEY);
    }

    // -------------------------------------------------------------------------
    // Get Organizational Structure (Public)
    // -------------------------------------------------------------------------

    public function test_get_organizational_structure_success(): void
    {
        $position = Position::factory()->create([
            'name' => 'Ketua Umum',
            'slug' => 'ketua-umum',
            'level' => 0,
            'is_active' => true,
        ]);

        Member::factory()->count(1)->create(['position_id' => $position->id]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Ketua Umum');
    }

    public function test_get_organizational_structure_only_returns_active_positions(): void
    {
        Position::factory()->create(['is_active' => true, 'parent_id' => null]);
        Position::factory()->inactive()->create(['parent_id' => null]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(1, 'data');
    }

    public function test_get_organizational_structure_with_nested_children(): void
    {
        $parent = Position::factory()->create([
            'name' => 'Ketua Umum',
            'slug' => 'ketua-umum',
            'level' => 0,
            'is_active' => true,
        ]);

        $child = Position::factory()->create([
            'name' => 'Divisi Akademik',
            'slug' => 'divisi-akademik',
            'parent_id' => $parent->id,
            'level' => 1,
            'is_active' => true,
        ]);

        Member::factory()->create(['position_id' => $parent->id]);
        Member::factory()->create(['position_id' => $child->id]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonCount(1, 'data.0.children');
        $response->assertJsonPath('data.0.children.0.name', 'Divisi Akademik');
    }

    public function test_get_organizational_structure_includes_members(): void
    {
        $position = Position::factory()->create([
            'is_active' => true,
            'parent_id' => null,
        ]);

        Member::factory()->count(3)->create([
            'position_id' => $position->id,
            'photo' => null,
        ]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data.0.assigned_members');
    }

    public function test_get_organizational_structure_excludes_inactive_children(): void
    {
        $parent = Position::factory()->create([
            'is_active' => true,
            'parent_id' => null,
        ]);

        Position::factory()->create([
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        Position::factory()->inactive()->create([
            'parent_id' => $parent->id,
        ]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(1, 'data.0.children');
    }

    public function test_get_organizational_structure_empty(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(0, 'data');
    }

    public function test_get_organizational_structure_uses_cached_positions(): void
    {
        $position = Position::factory()->create([
            'name' => 'Ketua Umum',
            'slug' => 'ketua-umum',
            'is_active' => true,
            'parent_id' => null,
        ]);

        $this->get($this->base_url)
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.name', 'Ketua Umum');

        DB::table('positions')
            ->where('id', $position->id)
            ->update(['name' => 'Ketua Baru']);

        $this->get($this->base_url)
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.name', 'Ketua Umum');
    }

    public function test_get_organizational_structure_cache_invalidates_when_position_updates(): void
    {
        $position = Position::factory()->create([
            'name' => 'Ketua Umum',
            'slug' => 'ketua-umum',
            'is_active' => true,
            'parent_id' => null,
        ]);

        $this->get($this->base_url)
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.name', 'Ketua Umum');

        $position->update(['name' => 'Ketua Baru']);

        $this->get($this->base_url)
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.name', 'Ketua Baru');
    }

    public function test_get_organizational_structure_cache_invalidates_when_member_updates(): void
    {
        $position = Position::factory()->create([
            'is_active' => true,
            'parent_id' => null,
        ]);
        $member = Member::factory()->create([
            'name' => 'Anggota Lama',
            'position_id' => $position->id,
            'photo' => null,
        ]);

        $this->get($this->base_url)
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.assigned_members.0.name', 'Anggota Lama');

        $member->update(['name' => 'Anggota Baru']);

        $this->get($this->base_url)
            ->assertValidResponse(200)
            ->assertJsonPath('data.0.assigned_members.0.name', 'Anggota Baru');
    }
}
