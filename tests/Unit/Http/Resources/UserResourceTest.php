<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\UserResource;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UserResource::class)]
class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resource_transforms_data_correctly()
    {
        $user = UserFactory::new()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'token' => 'some_random_token',
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(new Request);

        $this->assertSame('John Doe', $data['user']['name']);
        $this->assertSame('john@example.com', $data['user']['email']);
        $this->assertSame('some_random_token', $data['user']['token']);
        $this->assertTrue($data['user']['mobile_verified']);
        $this->assertTrue($data['user']['email_verified']);
    }

    public function test_mobile_verified_and_email_verified_should_be_false_when_corresponding_attributes_are_null()
    {
        $user = UserFactory::new()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'token' => 'another_random_token',
            'mobile_verified_at' => null,
            'email_verified_at' => null,
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(new Request);

        $this->assertFalse($data['user']['mobile_verified']);
        $this->assertFalse($data['user']['email_verified']);
    }

    public function test_get_full_mobile_number()
    {
        // Assuming getFullMobileNumber is a method on the User model that returns a formatted mobile number.
        $user = UserFactory::new()->create([
            'name' => 'Sam Smith',
            'email' => 'sam@example.com',
            'country_code' => '+1',
            'mobile' => '234567890',
            'token' => 'yet_another_token',
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(new Request);

        $this->assertSame('+1234567890', $data['user']['mobile']);
    }
}
