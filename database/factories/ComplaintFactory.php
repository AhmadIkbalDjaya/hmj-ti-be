<?php

namespace Database\Factories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
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
        $is_read = fake()->boolean();

        return [
            'name' => fake()->name(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'phone' => fake()->optional(0.5)->numerify('08##########'),
            'institute' => fake()->optional(0.6)->company(),
            'description' => fake()->paragraphs(rand(1, 3), true),
            'is_read' => $is_read,
            'read_at' => $is_read ? now() : null,
        ];
    }

    /**
     * Indicate that the complaint is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the complaint is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}
