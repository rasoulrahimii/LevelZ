<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\RegisterOnDeviceController;
use Database\Factories\SignupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterOnDeviceController::class)]
class RegisterOnDeviceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_registration_creates_user_and_deletes_signup(): void
    {
        $signup = SignupFactory::new()->create([
            'registration_token' => 'valid-token',
        ]);
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'registration_token' => 'valid-token',
        ];

        $response = $this->post(route('users.register.device'), $data);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'You have successfully registered.',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'country_code' => $signup->country_code,
            'mobile' => $signup->mobile,
        ]);
        $this->assertDatabaseMissing('signups', [
            'registration_token' => $data['registration_token'],
        ]);
    }

    public function test_registration_with_invalid_token_returns_error(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'registration_token' => 'invalid-token',
        ];

        $response = $this->post(route('users.register.device'), $data);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'You have not started the registration process yet.',
        ]);
    }

    public function test_registration_creates_user_with_token(): void
    {
        SignupFactory::new()->create([
            'registration_token' => 'unique-token',
            'mobile_verified_at' => now(),
        ]);
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'registration_token' => 'unique-token',
        ];

        $response = $this->post(route('users.register.device'), $data);
        $responseData = $response->json();

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'user' => [
                    'name',
                    'email',
                    'mobile',
                    'token',
                    'mobile_verified',
                    'email_verified',
                ],
            ],
        ]);
        $this->assertNotNull($responseData['data']['user']['token']);
        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'token' => $responseData['data']['user']['token'],
        ]);
    }
}
