<?php

namespace Database\Factories;

use App\Models\EngagementPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EngagementPackage>
 */
class EngagementPackageFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'title' => ucfirst(fake()->word()),
            'sort_order' => fake()->numberBetween(1, 99),
            'is_selectable' => true,
            'draft_data' => [
                'summary' => fake()->sentence(5),
                'audience' => fake()->sentence(),
                'inclusions' => [['label' => fake()->words(3, true)]],
                'service_ids' => [],
            ],
        ];
    }
}
