<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\RegisterMobileRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RegisterMobileRequest::class)]
class RegisterMobileRequestTest extends TestCase
{
    public function test_validation_passes_with_valid_data()
    {
        $data = [
            'mobile' => '1234567890',
            'country_code' => '+1',
        ];
        $request = new RegisterMobileRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_mobile_is_missing()
    {
        $data = [
            'country_code' => '+1',
        ];
        $request = new RegisterMobileRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('mobile', $validator->errors()->toArray());
    }

    public function test_validation_fails_for_invalid_mobile_format()
    {
        $data = [
            'mobile' => 'invalid_mobile',
            'country_code' => '+1',
        ];
        $request = new RegisterMobileRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('mobile', $validator->errors()->toArray());
    }

    public function test_validation_fails_when_country_code_is_missing()
    {
        $data = [
            'mobile' => '1234567890',
        ];
        $request = new RegisterMobileRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());
    }

    public function test_validation_fails_for_invalid_country_code_format()
    {
        $data = [
            'mobile' => '1234567890',
            'country_code' => '123',  // Invalid, should start with +
        ];
        $request = new RegisterMobileRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());
    }

    public function test_authorization_is_always_true()
    {
        $request = new RegisterMobileRequest;

        $this->assertTrue($request->authorize());
    }
}
