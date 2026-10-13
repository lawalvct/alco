<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Http\Requests\Leads\LeadRequest;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type' => LeadType::Contact,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'message' => fake()->sentence(12),
            'consent_text' => LeadRequest::CONSENT_TEXT,
            'consented_at' => now(),
            'status' => LeadStatus::New,
            'source_path' => '/contact',
        ];
    }

    public function consultation(): static
    {
        return $this->state(['type' => LeadType::Consultation, 'source_path' => '/book-consultation']);
    }

    public function status(LeadStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
