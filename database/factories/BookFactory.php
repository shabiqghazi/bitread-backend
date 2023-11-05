<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(rand(6, 20)),
            'cover' => "https://images.unsplash.com/photo-1697202120309-5b456026781a?auto=format&fit=crop&q=80&w=1470&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
            'writers' => fake()->name(),
            'user_id' => 1,
            'category' => "fiction",
            'description' => fake()->sentence(rand(40, 100)),
            'price' => "100.000",
            'shop_links' => json_encode(['shopee' => 'https://shopee.co.id/', 'tokopedia' => "https://tokopedia.co.id/", 'lazada' => "https://lazada.co.id/"]),
        ];
    }
}
