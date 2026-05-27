<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
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
            'photo' => fake()->optional(0.7)->randomElement([
                'members/photo1.jpg',
                'members/photo2.jpg',
                'members/photo3.jpg',
            ]),
            'position_id' => Position::factory(),
        ];
    }
}
