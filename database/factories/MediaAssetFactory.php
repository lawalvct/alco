<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Metadata-only assets: no file is written, so the dimensions given here
 * are kept. Use UploadedFile fakes when a test needs a real file.
 *
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'disk' => 'public',
            'path' => 'media/'.Str::uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 240_000,
            'width' => 2560,
            'height' => 820,
            'alt_text' => fake()->sentence(),
            'rights_confirmed' => true,
        ];
    }

    public function dimensions(int $width, int $height): static
    {
        return $this->state(['width' => $width, 'height' => $height]);
    }

    public function rightsUnconfirmed(): static
    {
        return $this->state(['rights_confirmed' => false]);
    }
}
