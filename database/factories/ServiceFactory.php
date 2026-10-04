<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
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
            'title' => fake()->words(2, true),
            'sort_order' => fake()->numberBetween(1, 99),
            'draft_data' => [
                'title' => fake()->words(2, true),
                'summary' => fake()->sentence(),
                'body' => fake()->paragraph(),
                'audience' => fake()->sentence(),
                'deliverables' => [],
            ],
        ];
    }
}
