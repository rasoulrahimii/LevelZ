<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\RegisterOnDeviceRequest;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterOnDeviceRequest::class)]
class RegisterOnDeviceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes_with_valid_data()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_name_is_missing()
    {
        $data = [
            'email' => 'john.doe@example.com',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_when_email_is_missing()
    {
        $data = [
            'name' => 'John Doe',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_when_registration_token_is_missing()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ];

        $request = new RegisterOnDeviceRequest;
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('registration_token', $validator->errors()->toArray());
    }

    public function test_validation_fails_for_invalid_email_format()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid_email',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_when_email_is_taken()
    {
        $existingUser = UserFactory::new()->create([
            'email' => 'john.doe@example.com',
        ]);
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'registration_token' => 'valid_token',
        ];
        $request = new RegisterOnDeviceRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_authorization_is_always_true()
    {
        $request = new RegisterOnDeviceRequest;

        $this->assertTrue($request->authorize());
    }
}
