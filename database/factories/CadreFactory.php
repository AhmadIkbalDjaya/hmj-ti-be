<?php

namespace Database\Factories;

use App\Enums\CadreStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cadre>
 */
class CadreFactory extends Factory
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
            'address' => fake()->address(),
            'batch' => (string) fake()->year(),
            'status' => fake()->randomElement(CadreStatus::cases()),
        ];
    }

    /**
     * Indicate that the cadre is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CadreStatus::ACTIVE->value,
        ]);
    }

    /**
     * Indicate that the cadre is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CadreStatus::INACTIVE->value,
        ]);
    }
}
