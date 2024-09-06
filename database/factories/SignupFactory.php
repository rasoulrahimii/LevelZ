<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Signup>
 */
class SignupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_code' => $this->faker->countryCode(),
            'mobile' => $this->faker->phoneNumber(),
            'verification_code' => $this->faker->randomNumber(6, true),
            'registration_token' => Str::random(60),
            'mobile_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
