<?php

namespace Tests\Feature\Guest;

use App\Models\OrganizationProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class AboutTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/about';

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    protected function createProfile(array $overrides = []): OrganizationProfile
    {
        return OrganizationProfile::create(array_merge([
            'goal' => 'Public goal',
            'vision' => 'Public vision',
            'missions' => [
                'Public mission one.',
                'Public mission two.',
            ],
            'main_image' => null,
            'secondary_image' => null,
        ], $overrides));
    }

    public function test_get_about_success(): void
    {
        $this->createProfile();

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonStructure([
            'message',
            'data' => ['goal', 'vision', 'missions', 'main_image', 'secondary_image'],
        ]);
        $response->assertJsonPath('data.goal', 'Public goal');
        $response->assertJsonPath('data.vision', 'Public vision');
        $response->assertJsonCount(2, 'data.missions');
        $this->assertArrayNotHasKey('id', $response->json('data'));
    }

    public function test_get_about_accessible_without_authentication(): void
    {
        $this->createProfile();

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);
    }
}
