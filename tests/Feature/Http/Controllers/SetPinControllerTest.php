<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\SetPinController;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(SetPinController::class)]
class SetPinControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_pin_setting_updates_user(): void
    {
        UserFactory::new()->create([
            'token' => 'valid-token',
        ]);
        $data = [
            'token' => 'valid-token',
            'pin' => '123456',
        ];

        $response = $this->postJson(route('users.set.pin'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'You have successfully set your PIN.',
        ]);
        // Fetch the user from the database and check if the PIN is hashed correctly
        $user = User::query()->where('token', $data['token'])->first();
        $this->assertNotNull($user);
        $this->assertTrue(password_verify($data['pin'], $user->pin));
    }

    public function test_setting_pin_with_invalid_token_returns_error(): void
    {
        $data = [
            'token' => 'invalid-token',
            'pin' => '123456',
        ];

        $response = $this->post(route('users.set.pin'), $data);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Invalid registration token',
        ]);
    }
}
