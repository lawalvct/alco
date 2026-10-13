<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMediaVariants;
use App\Models\MediaAsset;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('alco:optimise-media {--all : Rebuild every image, not only those still waiting or failed}')]
#[Description('Create the resized WebP copies used on the website for media-library images')]
class OptimiseMedia extends Command
{
    public function handle(): int
    {
        $assets = MediaAsset::query()
            ->when(! $this->option('all'), fn ($query) => $query->where(
                fn ($query) => $query->whereNull('variants_status')->orWhere('variants_status', '!=', 'ready'),
            ))
            ->get();

        if ($assets->isEmpty()) {
            $this->info('Every image is already optimised.');

            return self::SUCCESS;
        }

        $this->withProgressBar($assets, fn (MediaAsset $asset) => GenerateMediaVariants::dispatchSync($asset->id));
        $this->newLine();

        $failed = MediaAsset::query()->whereKey($assets->modelKeys())->where('variants_status', 'failed')->pluck('title');

        foreach ($failed as $title) {
            $this->warn("Could not optimise: {$title}");
        }

        $this->info(sprintf('Optimised %d of %d images.', $assets->count() - $failed->count(), $assets->count()));

        return $failed->isEmpty() ? self::SUCCESS : self::FAILURE;
    }
}
