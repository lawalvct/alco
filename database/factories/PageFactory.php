<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'title' => fake()->sentence(4),
            'template_key' => 'home',
            'draft_data' => [
                'title' => fake()->sentence(4),
                'hero_eyebrow' => 'ALCO Associate Solutions',
                'hero_line_one' => 'Your numbers, in order.',
                'hero_line_two' => 'Your business,',
                'hero_accent' => 'moving forward.',
                'hero_description' => fake()->sentence(),
                'blocks' => [],
            ],
        ];
    }
}
