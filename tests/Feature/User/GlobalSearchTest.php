<?php

namespace Tests\Feature\User;

use App\Models\Article;
use App\Models\Business;
use App\Models\Cadre;
use App\Models\Complaint;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected string $base_url = '/api/user/search';

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

    public function test_global_search_returns_grouped_results_across_all_modules(): void
    {
        $position = Position::factory()->create(['name' => 'Ketua Nusantara']);

        Article::factory()->create(['title' => 'Nusantara Tech']);
        Business::factory()->create(['title' => 'Merch Nusantara']);
        Member::factory()->create(['name' => 'Alya Nusantara', 'position_id' => $position->id]);
        Complaint::factory()->create(['name' => 'Laporan Nusantara']);
        Cadre::factory()->create(['name' => 'Rafi Nusantara']);

        Article::factory()->create(['title' => 'Unrelated Article']);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=Nusantara');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.query', 'Nusantara');
        $response->assertJsonPath('data.total', 6);
        $response->assertJsonCount(6, 'data.groups');
        $response->assertJsonPath('data.groups.0.type', 'articles');
        $response->assertJsonPath('data.groups.1.type', 'businesses');
        $response->assertJsonPath('data.groups.2.type', 'positions');
        $response->assertJsonPath('data.groups.3.type', 'members');
        $response->assertJsonPath('data.groups.4.type', 'complaints');
        $response->assertJsonPath('data.groups.5.type', 'cadres');
        $response->assertJsonPath('data.groups.0.results.0.title', 'Nusantara Tech');
        $response->assertJsonPath('data.groups.2.results.0.url', "/positions/{$position->id}");
    }

    public function test_global_search_requires_authentication(): void
    {
        $response = $this->get($this->base_url.'?search=Nusantara');

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    public function test_global_search_returns_empty_results_for_blank_search(): void
    {
        Article::factory()->create(['title' => 'Nusantara Tech']);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.query', '');
        $response->assertJsonPath('data.total', 0);
        $response->assertJsonPath('data.groups', []);
    }

    public function test_global_search_limit_applies_per_module(): void
    {
        Article::factory()->count(3)->create(['title' => 'Quantum Limit Needle']);
        Business::factory()->count(3)->create(['title' => 'Quantum Limit Needle']);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=Quantum%20Limit%20Needle&limit=2');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.total', 4);
        $response->assertJsonPath('data.groups.0.type', 'articles');
        $response->assertJsonPath('data.groups.0.count', 2);
        $response->assertJsonCount(2, 'data.groups.0.results');
        $response->assertJsonPath('data.groups.1.type', 'businesses');
        $response->assertJsonPath('data.groups.1.count', 2);
        $response->assertJsonCount(2, 'data.groups.1.results');
    }

    public function test_global_search_rejects_invalid_limit(): void
    {
        $response = $this->withToken($this->token)->get($this->base_url.'?search=Nusantara&limit=11');

        $response->assertValidResponse(422);
        $response->assertJsonValidationErrors(['limit']);
    }
}
