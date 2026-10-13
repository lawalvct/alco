<?php

namespace App\Support;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Typed, cached site settings. Defaults are the owner-confirmed business
 * details from the development plan, so the site renders correctly before
 * anyone opens the settings screen. Secrets never belong here.
 */
final class SiteSettings
{
    private const CACHE_KEY = 'site-settings';

    public const DEFAULTS = [
        'identity' => [
            'business_name' => 'ALCO Associate Solutions',
            'positioning_statement' => 'Professional Accounting Services. Bookkeeping | Financial Reporting | Tax Filings | Business Solutions',
            'business_category' => 'Accounting and Auditing Consultancy',
        ],
        'contact' => [
            'street_address' => '18, Lagos Street',
            'locality' => 'Ebute-Metta',
            'region' => 'Lagos State',
            'country' => 'Nigeria',
            'location_summary' => 'Ebute-Metta, Lagos, Nigeria',
            'email' => 'info@alcosolutions.ng',
            'phones' => [
                ['number' => '+2348140824230', 'whatsapp' => false],
                ['number' => '+2348148484955', 'whatsapp' => false],
            ],
        ],
        'social' => [
            'facebook_url' => null,
            'linkedin_url' => null,
            'tiktok_url' => null,
        ],
        'homepage' => [
            'hero_autoplay' => true,
            'hero_interval_seconds' => 8,
        ],
    ];

    private const SOCIAL_NETWORKS = [
        'facebook_url' => 'Facebook',
        'linkedin_url' => 'LinkedIn',
        'tiktok_url' => 'TikTok',
    ];

    /** @return array<string, mixed> */
    public static function get(string $group): array
    {
        return self::all()[$group] ?? [];
    }

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        /** @var array<string, array<string, mixed>> */
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = SiteSetting::query()->pluck('data', 'group');
            $settings = [];

            foreach (self::DEFAULTS as $group => $defaults) {
                $values = $stored->get($group);
                $settings[$group] = array_replace($defaults, is_array($values) ? $values : []);
            }

            return $settings;
        });
    }

    /** @param  array<string, mixed>  $data */
    public static function put(string $group, array $data, ?User $editor = null): void
    {
        $known = array_intersect_key($data, self::DEFAULTS[$group] ?? []);

        SiteSetting::query()->updateOrCreate(
            ['group' => $group],
            ['data' => $known, 'updated_by' => $editor?->id],
        );

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Only the fields visitors may see, shaped for the Vue layout.
     *
     * @return array{name: string, positioning: string, category: string, address: array{street: string, locality: string, region: string, country: string, summary: string}, email: string, phones: list<array{tel: string, display: string, whatsapp: string|null}>, social: list<array{label: string, url: string}>}
     */
    public static function publicPayload(): array
    {
        $identity = self::get('identity');
        $contact = self::get('contact');
        $social = self::get('social');

        $phones = [];

        foreach (is_array($contact['phones'] ?? null) ? $contact['phones'] : [] as $phone) {
            $number = is_array($phone) ? preg_replace('/[^\d+]/', '', (string) ($phone['number'] ?? '')) : '';

            if ($number === '' || $number === null) {
                continue;
            }

            $phones[] = [
                'tel' => $number,
                'display' => self::formatPhone($number),
                'whatsapp' => ! empty($phone['whatsapp']) ? 'https://wa.me/'.ltrim($number, '+') : null,
            ];
        }

        $links = [];

        foreach (self::SOCIAL_NETWORKS as $key => $label) {
            $url = $social[$key] ?? null;

            if (is_string($url) && str_starts_with($url, 'https://')) {
                $links[] = ['label' => $label, 'url' => $url];
            }
        }

        return [
            'name' => self::string($identity, 'business_name'),
            'positioning' => self::string($identity, 'positioning_statement'),
            'category' => self::string($identity, 'business_category'),
            'address' => [
                'street' => self::string($contact, 'street_address'),
                'locality' => self::string($contact, 'locality'),
                'region' => self::string($contact, 'region'),
                'country' => self::string($contact, 'country'),
                'summary' => self::string($contact, 'location_summary'),
            ],
            'email' => self::string($contact, 'email'),
            'phones' => $phones,
            'social' => $links,
        ];
    }

    /** @return array{autoplay: bool, interval_seconds: int} */
    public static function heroSlider(): array
    {
        $homepage = self::get('homepage');

        return [
            'autoplay' => (bool) ($homepage['hero_autoplay'] ?? true),
            'interval_seconds' => max(
                HeroImageSpec::MIN_AUTOPLAY_SECONDS,
                (int) ($homepage['hero_interval_seconds'] ?? 8),
            ),
        ];
    }

    /** Formats Nigerian numbers as "+234 814 082 4230"; other numbers are left as entered. */
    public static function formatPhone(string $number): string
    {
        return preg_match('/^\+234(\d{3})(\d{3})(\d{4})$/', $number, $parts)
            ? "+234 {$parts[1]} {$parts[2]} {$parts[3]}"
            : $number;
    }

    /** @param  array<string, mixed>  $values */
    private static function string(array $values, string $key): string
    {
        return is_string($values[$key] ?? null) ? $values[$key] : '';
    }
}
