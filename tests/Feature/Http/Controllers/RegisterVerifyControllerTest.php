<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\RegisterVerifyController;
use Database\Factories\SignupFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterVerifyController::class)]
class RegisterVerifyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_verification_updates_signup_and_returns_token(): void
    {
        $signup = SignupFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => 123456,
        ]);
        $data = [
            'country_code' => $signup->country_code,
            'mobile' => $signup->mobile,
            'verification_code' => $signup->verification_code,
        ];

        $response = $this->post(route('users.register.verify'), $data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'user',
            ],
            'registration_token',
        ]);
    }

    public function test_verification_with_invalid_code_returns_error(): void
    {
        $signup = SignupFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => 123456,
        ]);
        $data = [
            'country_code' => $signup->country_code,
            'mobile' => $signup->mobile,
            'verification_code' => 654321, // Invalid code
        ];

        $response = $this->post(route('users.register.verify'), $data);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Invalid verification code',
        ]);
    }

    public function test_verification_for_already_registered_user_returns_message(): void
    {
        $user = UserFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
        ]);
        $data = [
            'country_code' => $user->country_code,
            'mobile' => $user->mobile,
            'verification_code' => 123456,
        ];

        $response = $this->post(route('users.register.verify'), $data);

        $response->assertStatus(409);
        $response->assertJson([
            'message' => 'You are already registered.',
        ]);
    }
}
