<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\SetPinController;
use App\Http\Requests\SetPinRequest;
use App\Http\Resources\UserResource;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(SetPinController::class)]
class SetPinControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_error_for_invalid_token(): void
    {
        $data = [
            'token' => 'invalid_token',
            'pin' => '1234',
        ];
        $request = new SetPinRequest($data);

        $controller = new SetPinController;
        $response = $controller($request);
        $responseData = $response->getData(true);

        $this->assertSame(400, $response->status());
        $this->assertSame('Invalid registration token', $responseData['message']);
    }

    public function test_it_sets_pin_with_valid_token(): void
    {
        $user = UserFactory::new()->create([
            'token' => 'valid_token',
            'pin' => null, // Ensure the pin is null initially
        ]);
        $data = [
            'token' => 'valid_token',
            'pin' => '1234',
        ];
        $request = new SetPinRequest($data);

        $controller = new SetPinController;
        $response = $controller($request);

        $user->refresh();
        $this->assertTrue(password_verify('1234', $user->pin)); // Assert the pin is correctly hashed
        $this->assertSame('You have successfully set your PIN.', $response->additional['message']);
    }

    public function test_it_does_not_update_user_if_token_invalid(): void
    {
        $user = UserFactory::new()->create([
            'token' => 'valid_token',
            'pin' => null,
        ]);
        $data = [
            'token' => 'invalid_token',
            'pin' => '1234',
        ];
        $request = new SetPinRequest($data);

        $controller = new SetPinController;
        $response = $controller($request);

        $this->assertSame(400, $response->status());
        $user->refresh();
        $this->assertNull($user->pin);
    }

    public function test_it_returns_user_resource_when_pin_is_set(): void
    {
        $user = UserFactory::new()->create([
            'token' => 'valid_token',
        ]);
        $data = [
            'token' => 'valid_token',
            'pin' => '1234',
        ];
        $request = new SetPinRequest($data);

        $controller = new SetPinController;
        $response = $controller($request);

        $this->assertInstanceOf(UserResource::class, $response);
        $this->assertSame($user->id, $response->id);
        $this->assertSame('You have successfully set your PIN.', $response->additional['message']);
    }
}
