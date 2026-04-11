<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'phone' => fake()->optional(0.5)->numerify('08##########'),
            'institute' => fake()->optional(0.6)->company(),
            'description' => fake()->paragraphs(rand(1, 3), true),
        ];
    }
}
