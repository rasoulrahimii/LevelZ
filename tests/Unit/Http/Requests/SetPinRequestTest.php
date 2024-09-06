<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\SetPinRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(SetPinRequest::class)]
class SetPinRequestTest extends TestCase
{
    public function test_validation_passes_with_valid_data()
    {
        $data = [
            'token' => 'valid_token_string',
            'pin' => '123456',
        ];
        $request = new SetPinRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_token_is_missing()
    {
        $data = [
            'pin' => '123456',
        ];
        $request = new SetPinRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('token', $validator->errors()->toArray());
    }

    public function test_validation_fails_when_pin_is_missing()
    {
        $data = [
            'token' => 'valid_token_string',
        ];
        $request = new SetPinRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('pin', $validator->errors()->toArray());
    }

    public function test_validation_fails_for_pin_shorter_than_six_characters()
    {
        $data = [
            'token' => 'valid_token_string',
            'pin' => '12345', // Less than 6 characters
        ];
        $request = new SetPinRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('pin', $validator->errors()->toArray());
    }

    public function test_validation_fails_for_non_string_pin()
    {
        $data = [
            'token' => 'valid_token_string',
            'pin' => 123456, // Not a string
        ];
        $request = new SetPinRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('pin', $validator->errors()->toArray());
    }

    public function test_validation_fails_for_non_string_token()
    {
        $data = [
            'token' => 123456, // Not a string
            'pin' => '123456',
        ];
        $request = new SetPinRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('token', $validator->errors()->toArray());
    }

    public function test_authorization_is_always_true()
    {
        $request = new SetPinRequest;

        $this->assertTrue($request->authorize());
    }
}
