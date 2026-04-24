<?php

namespace Tests\Feature\User;

use App\Models\Article;
use App\Models\Business;
use App\Models\Complaint;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/dashboard/summary';

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

    public function test_get_dashboard_summary_success(): void
    {
        Article::factory()->count(3)->create();
        Business::factory()->count(2)->create();
        Member::factory()->count(5)->create();
        Complaint::factory()->count(1)->create();

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.articles.total', 3);
        $response->assertJsonPath('data.businesses.total', 2);
        $response->assertJsonPath('data.members.total', 5);
        $response->assertJsonPath('data.complaints.total', 1);
    }

    public function test_get_dashboard_summary_with_zero_counts(): void
    {
        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.articles.total', 0);
        $response->assertJsonPath('data.businesses.total', 0);
        $response->assertJsonPath('data.members.total', 0);
        $response->assertJsonPath('data.complaints.total', 0);
    }

    public function test_get_dashboard_summary_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }
}
