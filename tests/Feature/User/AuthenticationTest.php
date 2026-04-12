<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spectator\Spectator;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    public function test_login_success(): void
    {
        $response = $this->postJson('/api/user/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ]);

        $response->assertValidRequest();
        $response->assertValidResponse(200);
    }

    public function test_login_failed(): void
    {
        $response = $this->postJson('/api/user/login', [
            'username' => $this->user->username,
            'password' => 'wrong-password',
        ]);

        $response->assertValidRequest();
        $response->assertValidResponse(400);
    }

    public function test_login_validation_error(): void
    {
        $response = $this->postJson('/api/user/login', [
            'username' => '',
            'password' => '',
        ]);

        $response->assertValidResponse(422);
    }

    public function test_logout_success(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        // $loginResponse = $this->postJson('/api/user/login', [
        //     'username' => $this->user->username,
        //     'password' => 'password',
        // ]);
        // $token = $loginResponse->json('data.token');

        $response = $this->withToken($token)->get('/api/user/logout');

        $response->assertValidRequest();
        $response->assertValidResponse(200);
    }

    public function test_logout_failed(): void
    {
        $response = $this->get('/api/user/logout');

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }
}
