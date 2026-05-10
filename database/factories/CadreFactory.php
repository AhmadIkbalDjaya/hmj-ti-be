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
}
