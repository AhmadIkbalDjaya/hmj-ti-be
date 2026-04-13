<?php

namespace Tests\Feature\Guest;

use App\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/complaints';

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    // -------------------------------------------------------------------------
    // Store Complaint (Public)
    // -------------------------------------------------------------------------

    public function test_store_complaint_success(): void
    {
        $payload = [
            'name' => 'Ahmad Ikbal',
            'email' => 'ahmad@example.com',
            'phone' => '081234567890',
            'institute' => 'UIN Alauddin',
            'description' => 'Saran untuk perbaikan fasilitas.',
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(201);

        $response->assertJsonPath('data.name', $payload['name']);
        $response->assertJsonPath('data.description', $payload['description']);

        $this->assertDatabaseHas('complaints', [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'description' => $payload['description'],
        ]);
    }

    public function test_store_complaint_with_required_fields_only(): void
    {
        $payload = [
            'name' => 'John Doe',
            'description' => 'Keluhan tentang layanan.',
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidRequest();
        $response->assertValidResponse(201);

        $response->assertJsonPath('data.name', $payload['name']);

        $this->assertDatabaseHas('complaints', [
            'name' => $payload['name'],
            'description' => $payload['description'],
        ]);
    }

    public function test_store_complaint_validation_fails_when_name_missing(): void
    {
        $payload = [
            'description' => 'Some complaint.',
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_complaint_validation_fails_when_description_missing(): void
    {
        $payload = [
            'name' => 'Ahmad Ikbal',
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_complaint_validation_fails_when_all_fields_empty(): void
    {
        $payload = [
            'name' => '',
            'description' => '',
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['name', 'description']);
    }

    public function test_store_complaint_validation_fails_when_email_invalid(): void
    {
        $payload = [
            'name' => 'Ahmad Ikbal',
            'email' => 'not-an-email',
            'description' => 'Some complaint.',
        ];

        $response = $this->postJson($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['email']);
    }
}
