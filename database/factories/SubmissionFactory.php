<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => json_encode(['status_id' => rand(1, 4), 'label' => "status"]),
            'book_id' => 1,
            'last_message' => fake()->sentence(rand(40, 100)),
            'draft' => 'http://dummy_url.test/draft'
        ];
    }
}
