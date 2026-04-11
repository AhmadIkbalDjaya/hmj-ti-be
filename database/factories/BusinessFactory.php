<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(rand(2, 5), true);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'description' => fake()->paragraphs(rand(2, 5), true),
            'price' => fake()->randomElement([5000, 10000, 15000, 25000, 35000, 50000, 75000, 100000]),
            'image' => 'businesses/default.jpg',
            'whatsapp' => '08' . fake()->numerify('##########'),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the business is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
