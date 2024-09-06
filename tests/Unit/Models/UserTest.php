<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_full_mobile_number()
    {
        $user = UserFactory::new()->create([
            'country_code' => '+1',
            'mobile' => '1234567890',
        ]);

        $fullMobileNumber = $user->getFullMobileNumber();

        $this->assertSame('+11234567890', $fullMobileNumber);
    }
}
