<?php

namespace App\Support;

use App\Models\MediaAsset;

/**
 * Size rules for homepage hero slider images.
 *
 * The approved homepage design shows the slider as a wide banner below the
 * hero copy (roughly 3.1:1 on desktop and 10:7 on phones), so these limits
 * follow that frame rather than a full-screen 16:9 hero.
 */
final class HeroImageSpec
{
    public const MAX_VISIBLE_SLIDES = 10;

    public const MIN_AUTOPLAY_SECONDS = 7;

    public const DESKTOP_RECOMMENDED = '2560 × 820 px';

    public const DESKTOP_MIN_WIDTH = 1536;

    public const DESKTOP_MIN_HEIGHT = 480;

    public const DESKTOP_MIN_RATIO = 2.0;

    public const DESKTOP_MAX_RATIO = 3.6;

    public const MOBILE_RECOMMENDED = '1200 × 840 px';

    public const MOBILE_MIN_WIDTH = 750;

    public const MOBILE_MIN_HEIGHT = 500;

    public const MOBILE_MIN_RATIO = 1.2;

    public const MOBILE_MAX_RATIO = 1.8;

    public static function desktopGuidance(): string
    {
        return sprintf(
            'Use a wide landscape photo, ideally %s (about 3:1), at least %d × %d px. Keep the subject clear of the edges and avoid text in the image.',
            self::DESKTOP_RECOMMENDED,
            self::DESKTOP_MIN_WIDTH,
            self::DESKTOP_MIN_HEIGHT,
        );
    }

    public static function mobileGuidance(): string
    {
        return sprintf(
            'Optional. Shown on phones instead of the main image, ideally %s, at least %d × %d px.',
            self::MOBILE_RECOMMENDED,
            self::MOBILE_MIN_WIDTH,
            self::MOBILE_MIN_HEIGHT,
        );
    }

    public static function desktopProblem(MediaAsset $asset): ?string
    {
        return self::problem(
            $asset,
            self::DESKTOP_MIN_WIDTH,
            self::DESKTOP_MIN_HEIGHT,
            self::DESKTOP_MIN_RATIO,
            self::DESKTOP_MAX_RATIO,
        );
    }

    public static function mobileProblem(MediaAsset $asset): ?string
    {
        return self::problem(
            $asset,
            self::MOBILE_MIN_WIDTH,
            self::MOBILE_MIN_HEIGHT,
            self::MOBILE_MIN_RATIO,
            self::MOBILE_MAX_RATIO,
        );
    }

    private static function problem(MediaAsset $asset, int $minWidth, int $minHeight, float $minRatio, float $maxRatio): ?string
    {
        if (! $asset->width || ! $asset->height) {
            return 'The image size could not be read. Upload a JPEG, PNG or WebP file.';
        }

        if ($asset->width < $minWidth || $asset->height < $minHeight) {
            return sprintf(
                'This image is %s. It must be at least %d × %d px.',
                $asset->dimensionsLabel(),
                $minWidth,
                $minHeight,
            );
        }

        $ratio = $asset->width / $asset->height;

        if ($ratio < $minRatio || $ratio > $maxRatio) {
            return sprintf(
                'This image is %s (%.1f:1). Its width must be between %.1f and %.1f times its height.',
                $asset->dimensionsLabel(),
                $ratio,
                $minRatio,
                $maxRatio,
            );
        }

        return null;
    }
}
