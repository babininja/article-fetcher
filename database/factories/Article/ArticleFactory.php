<?php

namespace Database\Factories\Article;

use App\Enums\Fetch\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'source' => $this->faker->title(),
            'provider' => $this->faker->randomElement([Provider::NEWSAPI,Provider::NYT,Provider::GUARDIAN]),
            'author' => $this->faker->name(),
            'title' => $this->faker->title(),
            'content' => $this->faker->paragraph(),
            'category' => $this->faker->title(),
            'published_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
