<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\RegisterOnDeviceController;
use App\Http\Requests\RegisterOnDeviceRequest;
use App\Models\Signup;
use App\Models\User;
use Database\Factories\SignupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterOnDeviceController::class)]
class RegisterOnDeviceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_error_if_no_registration_started(): void
    {
        $data = ['registration_token' => 'invalid_token'];
        $request = new RegisterOnDeviceRequest($data);

        $controller = new RegisterOnDeviceController;
        $response = $controller($request);
        $responseData = $response->getData(true);

        $this->assertSame(400, $response->status());
        $this->assertSame('You have not started the registration process yet.', $responseData['message']);
    }

    public function test_it_creates_user_with_valid_data(): void
    {
        // Simulate an existing signup record
        $signup = SignupFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
            'registration_token' => 'valid_token',
            'mobile_verified_at' => now(),
        ]);
        // Provide matching valid data
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest($data);

        $controller = new RegisterOnDeviceController;
        $controller($request);

        // Check that the user was created in the database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'country_code' => $signup->country_code,
            'mobile' => $signup->mobile,
            'mobile_verified_at' => $signup->mobile_verified_at,
        ]);
    }

    public function test_it_deletes_signup_data_after_successful_operation(): void
    {
        // Simulate an existing signup record
        $signup = SignupFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
            'registration_token' => 'valid_token',
            'mobile_verified_at' => now(),
        ]);
        // Provide matching valid data
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest($data);

        $controller = new RegisterOnDeviceController;
        $response = $controller($request);

        // Check that the signup record was deleted
        $this->assertDatabaseMissing('signups', ['registration_token' => 'valid_token']);
    }

    public function test_it_generates_unique_token_for_new_user(): void
    {
        // Simulate a signup record
        SignupFactory::new()->create([
            'registration_token' => 'valid_token',
            'mobile' => '1234567890',
        ]);
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest($data);

        $controller = new RegisterOnDeviceController;
        $controller($request);

        // Check that a unique token was generated for the new user
        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(60, strlen($user->token));
    }

    public function test_it_does_not_register_if_signup_record_not_found(): void
    {
        $data = ['registration_token' => 'invalid_token'];
        $request = new RegisterOnDeviceRequest($data);

        $controller = new RegisterOnDeviceController;
        $response = $controller($request);
        $responseData = $response->getData(true);

        $this->assertSame(400, $response->status());
        $this->assertSame('You have not started the registration process yet.', $responseData['message']);
    }
}
