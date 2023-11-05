<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => 1,
            'title' => fake()->sentence(rand(6, 20)),
            'status' => json_encode(['status_id' => rand(1, 2), 'label' => "status"]),
            'text' => fake()->sentence(rand(300, 500)),
            'image' => "https://images.unsplash.com/photo-1697202120309-5b456026781a?auto=format&fit=crop&q=80&w=1470&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
        ];
    }
}
