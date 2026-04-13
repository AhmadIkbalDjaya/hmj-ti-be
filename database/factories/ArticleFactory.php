<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(rand(3, 6), true);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'content' => fake()->paragraphs(rand(3, 8), true),
            'publish_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'image' => 'articles/default.jpg',
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
        ];
    }

    /**
     * Indicate that the article is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the article is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
