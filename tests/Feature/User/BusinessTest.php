<?php

namespace Tests\Feature\User;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spectator\Spectator;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/businesses';

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
    // Index Businesses
    // -------------------------------------------------------------------------

    public function test_get_businesses_success(): void
    {
        Business::factory()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_businesses_with_pagination(): void
    {
        Business::factory()->count(15)->create();

        $response = $this->withToken($this->token)->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_get_businesses_with_search(): void
    {
        Business::factory()->create(['title' => 'Kaos HMJ TI']);
        Business::factory()->create(['title' => 'Stiker Logo', 'description' => 'Kaos premium quality']);
        Business::factory()->create(['title' => 'Topi Snapback']);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=Kaos');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_businesses_with_is_active_filter(): void
    {
        Business::factory()->count(2)->create(['is_active' => true]);
        Business::factory()->inactive()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url.'?is_active=1');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_businesses_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    // -------------------------------------------------------------------------
    // Show Business
    // -------------------------------------------------------------------------

    public function test_show_business_success(): void
    {
        $business = Business::factory()->create();

        $response = $this->withToken($this->token)->get("{$this->base_url}/{$business->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.id', $business->id);
        $response->assertJsonPath('data.title', $business->title);
        $response->assertJsonPath('data.slug', $business->slug);
        $response->assertJsonPath('data.description', $business->description);
        $response->assertJsonPath('data.whatsapp', $business->whatsapp);
    }

    public function test_show_business_unauthenticated(): void
    {
        $business = Business::factory()->create();

        $response = $this->get("{$this->base_url}/{$business->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    public function test_show_business_not_found(): void
    {
        $response = $this->withToken($this->token)->get("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Store Business
    // -------------------------------------------------------------------------

    public function test_store_business_success(): void
    {
        $this->fakeStorage();

        $payload = [
            'title' => 'Kaos HMJ TI',
            'slug' => 'kaos-hmj-ti',
            'description' => 'Kaos resmi HMJ TI UINAM.',
            'price' => 75000,
            'image' => UploadedFile::fake()->image('business.jpg'),
            'whatsapp' => '081234567890',
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(201);

        $response->assertJsonPath('data.title', $payload['title']);
        $response->assertJsonPath('data.slug', $payload['slug']);

        $this->assertDatabaseHas('businesses', [
            'title' => $payload['title'],
            'slug' => $payload['slug'],
        ]);
    }

    public function test_store_business_unauthenticated(): void
    {
        $payload = [
            'title' => 'Kaos HMJ TI',
            'slug' => 'kaos-hmj-ti',
            'description' => 'Kaos resmi HMJ TI UINAM.',
            'price' => 75000,
            'image' => 'https://example.com/images/business.jpg',
            'whatsapp' => '081234567890',
            'is_active' => true,
        ];

        $response = $this->post($this->base_url, $payload);

        $response->assertValidResponse(401);
    }

    public function test_store_business_validation_fails_when_fields_missing(): void
    {
        $payload = [
            'title' => '',
            'slug' => '',
            'description' => '',
            'price' => '',
            'image' => '',
            'whatsapp' => '',
            'is_active' => '',
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'title', 'slug', 'description', 'price', 'image', 'whatsapp', 'is_active',
        ]);
    }

    public function test_store_business_validation_fails_when_slug_is_duplicate(): void
    {
        Business::factory()->create(['slug' => 'existing-slug']);

        $payload = [
            'title' => 'Another Business',
            'slug' => 'existing-slug',
            'description' => 'Some description here.',
            'price' => 50000,
            'image' => 'https://example.com/images/business.jpg',
            'whatsapp' => '081234567890',
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['slug']);
    }

    public function test_store_business_validation_fails_when_price_is_not_integer(): void
    {
        $payload = [
            'title' => 'Kaos HMJ TI',
            'slug' => 'kaos-hmj-ti',
            'description' => 'Kaos resmi HMJ TI UINAM.',
            'price' => 'not-a-number',
            'image' => 'https://example.com/images/business.jpg',
            'whatsapp' => '081234567890',
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['price']);
    }

    // -------------------------------------------------------------------------
    // Update Business
    // -------------------------------------------------------------------------

    public function test_update_business_success(): void
    {
        $business = Business::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'description' => 'Updated description.',
            'price' => 100000,
            'image' => null,
            'whatsapp' => '089876543210',
            'is_active' => false,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$business->id}", $payload);

        $response->assertValidResponse(200);

        $response->assertJsonPath('data.title', 'Updated Title');
        $response->assertJsonPath('data.slug', 'updated-title');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
        ]);
    }

    public function test_update_business_with_image(): void
    {
        $this->fakeStorage();

        $oldImage = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldImage->store('businesses');

        $business = Business::factory()->create(['image' => $oldPath]);

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'description' => 'Updated description.',
            'price' => 100000,
            'image' => UploadedFile::fake()->image('new.jpg'),
            'whatsapp' => '089876543210',
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$business->id}", $payload);

        $response->assertValidResponse(200);

        Storage::assertMissing($oldPath);

        $business->refresh();
        Storage::assertExists($business->image);
    }

    public function test_update_business_unauthenticated(): void
    {
        $business = Business::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'description' => 'Updated description.',
            'price' => 100000,
            'whatsapp' => '089876543210',
            'is_active' => true,
        ];

        $response = $this->put("{$this->base_url}/{$business->id}", $payload);

        $response->assertValidResponse(401);
    }

    public function test_update_business_not_found(): void
    {
        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'description' => 'Updated description.',
            'price' => 100000,
            'whatsapp' => '089876543210',
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/999", $payload);

        $response->assertValidResponse(404);
    }

    public function test_update_business_validation_fails_when_fields_missing(): void
    {
        $business = Business::factory()->create();

        $payload = [
            'title' => '',
            'slug' => '',
            'description' => '',
            'price' => '',
            'whatsapp' => '',
            'is_active' => '',
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$business->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'title', 'slug', 'description', 'price', 'whatsapp', 'is_active',
        ]);
    }

    public function test_update_business_validation_fails_when_slug_is_duplicate(): void
    {
        Business::factory()->create(['slug' => 'taken-slug']);
        $business = Business::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'taken-slug',
            'description' => 'Updated description.',
            'price' => 100000,
            'whatsapp' => '089876543210',
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$business->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['slug']);
    }

    // -------------------------------------------------------------------------
    // Destroy Business
    // -------------------------------------------------------------------------

    public function test_destroy_business_success(): void
    {
        $this->fakeStorage();

        $image = UploadedFile::fake()->image('business.jpg');
        $imagePath = $image->store('businesses');

        $business = Business::factory()->create(['image' => $imagePath]);

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$business->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('businesses', ['id' => $business->id]);
        Storage::assertMissing($imagePath);
    }

    public function test_destroy_business_without_image(): void
    {
        $business = Business::factory()->create(['image' => '']);

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$business->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('businesses', ['id' => $business->id]);
    }

    public function test_destroy_business_unauthenticated(): void
    {
        $business = Business::factory()->create();

        $response = $this->deleteJson("{$this->base_url}/{$business->id}");

        $response->assertValidResponse(401);
    }

    public function test_destroy_business_not_found(): void
    {
        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }
}
