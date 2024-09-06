<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\RegisterMobileController;
use App\Http\Requests\RegisterMobileRequest;
use Database\Factories\SignupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterMobileController::class)]
class RegisterMobileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_json_response(): void
    {
        $data = ['country_code' => '+1', 'mobile' => '1234567890'];
        $request = new RegisterMobileRequest($data);

        $controller = new RegisterMobileController;
        $response = $controller($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_it_creates_a_signup_record(): void
    {
        $data = ['country_code' => '+1', 'mobile' => '1234567890'];
        $request = new RegisterMobileRequest($data);

        $controller = new RegisterMobileController;
        $controller($request);

        $this->assertDatabaseHas('signups', ['country_code' => '+1', 'mobile' => '1234567890']);
    }

    public function test_it_updates_verification_code_and_registration_token_when_the_mobile_exists(): void
    {
        $data = ['country_code' => '+1', 'mobile' => '1234567890'];
        $signupRecord = SignupFactory::new()->create($data);
        $request = new RegisterMobileRequest($data);

        $controller = new RegisterMobileController;
        $controller($request);

        $this->assertDatabaseHas('signups', ['country_code' => $signupRecord->country_code, 'mobile' => $signupRecord->mobile]);
        $this->assertDatabaseMissing('signups', ['verification_code' => '+1', 'registration_token' => $signupRecord->registration_token]);
    }

    public function test_it_does_not_allow_for_repetitive_successful_request_before_two_minutes(): void
    {
        $data = ['country_code' => '+1', 'mobile' => '1234567890'];
        $request = new RegisterMobileRequest($data);

        $controller = new RegisterMobileController;
        $controller($request); // first successful attempt
        $secondResponse = $controller($request); // second attempt

        $responseData = $secondResponse->getData(true);
        $this->assertSame(400, $secondResponse->status());
        $this->assertSame('At least 2 minutes must pass since your last successful attempt.', $responseData['message']);
    }

    public function test_it_generates_six_digits_verification_code(): void
    {
        $data = ['country_code' => '+1', 'mobile' => '1234567890'];
        $request = new RegisterMobileRequest($data);

        $controller = new RegisterMobileController;
        $response = $controller($request);
        $responseData = $response->getData(true);

        $this->assertGreaterThanOrEqual(100000, $responseData['mobile_verification_code']);
        $this->assertLessThanOrEqual(999999, $responseData['mobile_verification_code']);
    }
}
