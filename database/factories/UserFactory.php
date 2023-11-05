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
    public function definition(): array
    {
        // $table->id();
        // $table->string('name');
        // $table->string('email')->unique();
        // $table->timestamp('email_verified_at')->nullable();
        // $table->string('password');
        // $table->json('role');
        // $table->string('gender')->nullable();
        // $table->text('bio')->nullable();
        // $table->json('social')->nullable();
        // $table->rememberToken();
        // $table->timestamps();
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => json_encode(['role_id' => rand(1, 4), 'label' => 'role_label']),
            'gender' => 'Male',
            'bio' => fake()->sentence(30, 50),
            'social' => json_encode(['shopee' => 'https://shopee.co.id/', 'tokopedia' => "https://tokopedia.co.id/", 'lazada' => "https://lazada.co.id/"]),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
