<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_first_name' => fake()->firstName(),
            'user_last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'username' => Str::random(6),
            'password' => '$2a$12$JUx1b3crprfdJwF2eRsRhO7XjvUj/poodayp8nuCIzTMSnIlsR4ii', //for sample
            'icon' => '/user_images/profile_pictures/picture.png',
            'present_status' => 'online',
            'last_online' => now(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
