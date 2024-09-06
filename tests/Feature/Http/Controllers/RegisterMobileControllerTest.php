<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\RegisterMobileController;
use App\Models\Signup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterMobileController::class)]
class RegisterMobileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_registration_creates_or_updates_signup_record(): void
    {
        $data = [
            'country_code' => '+1',
            'mobile' => '1234567890',
        ];

        $response = $this->post(route('users.register.mobile'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Your request has been fulfilled successfully.',
            'exists' => false,
            'mobile_verified' => false,
        ]);
        $this->assertDatabaseHas('signups', [
            'country_code' => $data['country_code'],
            'mobile' => $data['mobile'],
        ]);
        // Check if verification code and registration token are present
        $signup = Signup::query()->where('country_code', $data['country_code'])->where('mobile', $data['mobile'])->first();
        $this->assertNotNull($signup->verification_code);
        $this->assertNotNull($signup->registration_token);
    }

    public function test_throttles_repetitive_successful_requests(): void
    {
        $data = [
            'country_code' => '+1',
            'mobile' => '1234567890',
        ];

        $response1 = $this->post(route('users.register.mobile'), $data); // Make the first request
        $response2 = $this->post(route('users.register.mobile'), $data); // Make a second request within 2 minutes

        $response1->assertStatus(200);
        $response2->assertStatus(400);
        $response2->assertJson([
            'message' => 'At least 2 minutes must pass since your last successful attempt.',
        ]);
    }

    /**
     * Test verification code generation.
     */
    public function test_verification_code_generation(): void
    {
        $data = [
            'country_code' => '+1',
            'mobile' => '1234567890',
        ];

        $response = $this->postJson(route('users.register.mobile'), $data);
        $responseData = $response->json();

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'exists',
            'mobile_verified',
            'mobile_verification_code',
        ]);
        $this->assertIsInt($responseData['mobile_verification_code']);
        $this->assertGreaterThanOrEqual(100000, $responseData['mobile_verification_code']);
        $this->assertLessThanOrEqual(999999, $responseData['mobile_verification_code']);
    }
}
