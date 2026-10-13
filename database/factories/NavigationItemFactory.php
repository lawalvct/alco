<?php

namespace Database\Factories;

use App\Models\NavigationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationItem>
 */
class NavigationItemFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'location' => 'header',
            'label' => ucfirst(fake()->word()),
            'url' => '/'.fake()->slug(1),
            'sort_order' => fake()->numberBetween(1, 99),
            'is_visible' => true,
            'opens_in_new_tab' => false,
        ];
    }
}
