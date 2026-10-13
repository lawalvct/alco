<?php

namespace App\Jobs;

use App\Models\MediaAsset;
use GdImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Builds resized WebP copies of an uploaded image so pages can serve a
 * small file to phones and a sharp one to large screens. Re-encoding also
 * drops camera metadata. The original upload is kept untouched.
 */
class GenerateMediaVariants implements ShouldQueue
{
    use Queueable;

    /** Widths offered in srcset; never larger than the original. */
    public const WIDTHS = [640, 1024, 1600, 2400];

    public const MAX_WIDTH = 2560;

    private const QUALITY = 80;

    public int $tries = 2;

    public function __construct(public int $mediaAssetId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $asset = MediaAsset::find($this->mediaAssetId);

        if (! $asset) {
            return;
        }

        $disk = Storage::disk($asset->disk);

        if (! in_array($asset->mime_type, MediaAsset::ACCEPTED_MIME_TYPES, true) || ! $disk->exists($asset->path)) {
            $asset->forceFill(['variants' => null, 'variants_status' => 'failed'])->saveQuietly();

            return;
        }

        try {
            $source = imagecreatefromstring((string) $disk->get($asset->path));

            if (! $source instanceof GdImage) {
                throw new RuntimeException("Could not read image {$asset->path}.");
            }

            $directory = $asset->variantsDirectory();
            $disk->deleteDirectory($directory);
            $variants = [];

            foreach ($this->targetWidths(imagesx($source)) as $width) {
                $variants[] = $this->writeVariant($source, $width, $asset, "{$directory}/{$width}.webp");
            }

            $asset->forceFill(['variants' => $variants, 'variants_status' => 'ready'])->saveQuietly();
        } catch (Throwable $exception) {
            $asset->forceFill(['variants' => null, 'variants_status' => 'failed'])->saveQuietly();

            report($exception);
        }
    }

    /** @return list<int> */
    private function targetWidths(int $originalWidth): array
    {
        $widths = array_filter(self::WIDTHS, fn (int $width): bool => $width < $originalWidth);
        $widths[] = min($originalWidth, self::MAX_WIDTH);

        $widths = array_values(array_unique($widths));
        sort($widths);

        return $widths;
    }

    /** @return array{width: int, height: int, path: string, size: int} */
    private function writeVariant(GdImage $source, int $width, MediaAsset $asset, string $path): array
    {
        $scaled = imagescale($source, $width, -1, IMG_BICUBIC);

        if (! $scaled instanceof GdImage) {
            throw new RuntimeException("Could not resize image {$asset->path} to {$width}px.");
        }

        // Keep transparency from PNG and WebP sources.
        imagepalettetotruecolor($scaled);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);

        ob_start();
        imagewebp($scaled, null, self::QUALITY);
        $bytes = (string) ob_get_clean();

        Storage::disk($asset->disk)->put($path, $bytes, 'public');

        return [
            'width' => $width,
            'height' => imagesy($scaled),
            'path' => $path,
            'size' => strlen($bytes),
        ];
    }
}
