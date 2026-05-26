<?php

namespace Tests\Feature\User;

use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spectator\Spectator;
use Tests\TestCase;

class OrganizationProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/organization-profile';

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
        config(['filesystems.default' => 'public']);
    }

    protected function createProfile(array $overrides = []): OrganizationProfile
    {
        return OrganizationProfile::create(array_merge([
            'goal' => 'Initial goal',
            'vision' => 'Initial vision',
            'missions' => [
                'Initial mission one.',
                'Initial mission two.',
            ],
            'main_image' => null,
            'secondary_image' => null,
        ], $overrides));
    }

    public function test_get_organization_profile_success(): void
    {
        $profile = $this->createProfile();

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.id', $profile->id);
        $response->assertJsonPath('data.goal', 'Initial goal');
        $response->assertJsonCount(2, 'data.missions');
    }

    public function test_get_organization_profile_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    public function test_update_organization_profile_success(): void
    {
        $this->createProfile();

        $payload = [
            'goal' => 'Updated goal',
            'vision' => 'Updated vision',
            'missions' => [
                'Updated mission one.',
                'Updated mission two.',
                'Updated mission three.',
            ],
        ];

        $response = $this->withToken($this->token)
            ->putJson($this->base_url, $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.goal', 'Updated goal');
        $response->assertJsonPath('data.vision', 'Updated vision');
        $response->assertJsonCount(3, 'data.missions');

        $this->assertDatabaseCount('organization_profiles', 1);
        $this->assertDatabaseHas('organization_profiles', [
            'id' => 1,
            'goal' => 'Updated goal',
            'vision' => 'Updated vision',
        ]);
    }

    public function test_update_organization_profile_replaces_images(): void
    {
        $this->fakeStorage();

        $oldMainImage = UploadedFile::fake()->image('old-main.jpg')->store('organization-profiles');
        $oldSecondaryImage = UploadedFile::fake()->image('old-secondary.jpg')->store('organization-profiles');

        $profile = $this->createProfile([
            'main_image' => $oldMainImage,
            'secondary_image' => $oldSecondaryImage,
        ]);

        $response = $this->withToken($this->token)
            ->put($this->base_url, [
                'goal' => 'Image goal',
                'vision' => 'Image vision',
                'missions' => [
                    'Mission with image one.',
                    'Mission with image two.',
                ],
                'main_image' => UploadedFile::fake()->image('new-main.jpg'),
                'secondary_image' => UploadedFile::fake()->image('new-secondary.jpg'),
            ]);

        $response->assertValidResponse(200);

        Storage::assertMissing($oldMainImage);
        Storage::assertMissing($oldSecondaryImage);

        $profile->refresh();

        $this->assertNotEquals($oldMainImage, $profile->main_image);
        $this->assertNotEquals($oldSecondaryImage, $profile->secondary_image);
        Storage::assertExists($profile->main_image);
        Storage::assertExists($profile->secondary_image);
    }

    public function test_update_organization_profile_unauthenticated(): void
    {
        $response = $this->putJson($this->base_url, [
            'goal' => 'Updated goal',
            'vision' => 'Updated vision',
            'missions' => ['Updated mission.'],
        ]);

        $response->assertValidResponse(401);
    }

    public function test_update_organization_profile_validation_fails_when_fields_missing(): void
    {
        $response = $this->withToken($this->token)
            ->putJson($this->base_url, [
                'goal' => '',
                'vision' => '',
                'missions' => '',
            ]);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['goal', 'vision', 'missions']);
    }

    public function test_update_organization_profile_validation_fails_when_missions_empty(): void
    {
        $response = $this->withToken($this->token)
            ->putJson($this->base_url, [
                'goal' => 'Updated goal',
                'vision' => 'Updated vision',
                'missions' => [],
            ]);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['missions']);
    }

    public function test_update_organization_profile_validation_fails_when_mission_item_invalid(): void
    {
        $response = $this->withToken($this->token)
            ->putJson($this->base_url, [
                'goal' => 'Updated goal',
                'vision' => 'Updated vision',
                'missions' => [
                    'Valid mission.',
                    ['Invalid mission.'],
                ],
            ]);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['missions.1']);
    }

    public function test_update_organization_profile_validation_fails_when_image_invalid(): void
    {
        $this->fakeStorage();

        $response = $this->withToken($this->token)
            ->put($this->base_url, [
                'goal' => 'Updated goal',
                'vision' => 'Updated vision',
                'missions' => ['Updated mission.'],
                'main_image' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
            ]);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['main_image']);
    }
}
