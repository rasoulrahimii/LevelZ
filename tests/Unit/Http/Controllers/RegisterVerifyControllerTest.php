<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\RegisterVerifyController;
use App\Http\Requests\RegisterVerifyRequest;
use App\Models\Signup;
use App\Models\User;
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

    public function test_it_returns_user_if_already_registered(): void
    {
        // Simulate an existing registered user
        $user = UserFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
        ]);
        $data = [
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => '123456', // Doesn't matter since the user is already registered
        ];
        $request = new RegisterVerifyRequest($data);

        $controller = new RegisterVerifyController;
        $response = $controller($request);
        $responseData = $response->getData(true);

        $this->assertSame($user->id, $response->original->id); // Ensure user data is returned
        $this->assertSame('You are already registered.', $responseData['message']);
    }

    public function test_it_throws_error_if_signup_record_not_found(): void
    {
        $data = [
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => '123456',
        ];
        $request = new RegisterVerifyRequest($data);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Almost user does not exist');

        $controller = new RegisterVerifyController;
        $controller($request);
    }

    public function test_it_returns_error_for_invalid_verification_code(): void
    {
        // Simulate a signup record with a different verification code
        $signup = SignupFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => '654321',
        ]);
        $data = [
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => '123456', // Mismatched verification code
        ];
        $request = new RegisterVerifyRequest($data);

        $controller = new RegisterVerifyController;
        $response = $controller($request);
        $responseData = $response->getData(true);

        $this->assertSame(400, $response->status());
        $this->assertSame('Invalid verification code', $responseData['message']);
    }

    public function test_it_verifies_mobile_and_returns_registration_token_on_valid_data(): void
    {
        // Simulate a signup record with a matching verification code
        $signup = SignupFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => '123456',
        ]);
        $data = [
            'country_code' => '+1',
            'mobile' => '1234567890',
            'verification_code' => '123456',
        ];
        $request = new RegisterVerifyRequest($data);

        $controller = new RegisterVerifyController;
        $response = $controller($request);
        $responseData = $response->getData(true);

        $this->assertSame($signup->registration_token, $responseData['registration_token']);
        $this->assertNotNull(Signup::where('mobile', '1234567890')->first()->mobile_verified_at);
    }

    public function test_it_does_not_proceed_if_signup_record_is_not_found(): void
    {
        $data = [
            'country_code' => '+1',
            'mobile' => '0987654321', // Different mobile number, no matching record
            'verification_code' => '123456',
        ];
        $request = new RegisterVerifyRequest($data);
        $controller = new RegisterVerifyController;

        $this->expectException(ModelNotFoundException::class);

        $controller($request);
    }
}
