<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'question' => rtrim(fake()->sentence(6), '.').'?',
            'answer' => fake()->paragraph(),
            'group' => 'General',
            'sort_order' => fake()->numberBetween(1, 99),
            'is_published' => true,
        ];
    }
}
