<?php

namespace Database\Factories;

use App\Models\HomepageHeroSlide;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageHeroSlide>
 */
class HomepageHeroSlideFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'media_asset_id' => MediaAsset::factory(),
            'focal_x' => 50,
            'focal_y' => 50,
            'is_decorative' => false,
            'sort_order' => fake()->numberBetween(1, 99),
            'is_visible' => false,
        ];
    }

    public function visible(): static
    {
        return $this->state(['is_visible' => true]);
    }
}
