<?php

namespace App\Support;

use App\Models\NavigationItem;
use Illuminate\Support\Facades\Cache;

/**
 * Header and footer menus for the public layout, cached until an item
 * changes. An empty menu falls back to the defaults so the site always
 * has working navigation.
 */
final class Navigation
{
    /** Only saved links are cached; fallbacks are applied on read. */
    private const CACHE_KEY = 'site-navigation.items';

    /** Internal paths offered to editors; external links must use https. */
    public const INTERNAL_PAGES = [
        '/' => 'Home',
        '/about' => 'About',
        '/services' => 'Services',
        '/insights' => 'Insights',
        '/tools' => 'Tools',
        '/faq' => 'FAQ',
        '/contact' => 'Contact',
        '/book-consultation' => 'Book a consultation',
    ];

    private const DEFAULTS = [
        'header' => ['/', '/about', '/services', '/insights', '/tools', '/contact'],
        'footer' => ['/', '/about', '/services', '/insights', '/tools', '/faq', '/contact'],
    ];

    /** @return array<string, list<array{label: string, href: string, external: bool}>> */
    public static function menus(): array
    {
        /** @var list<array{location: string, label: string, href: string, external: bool}> $items */
        $items = Cache::rememberForever(self::CACHE_KEY, fn (): array => NavigationItem::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (NavigationItem $item): array => [
                'location' => $item->location,
                'label' => $item->label,
                'href' => $item->url,
                'external' => $item->isExternal() || $item->opens_in_new_tab,
            ])
            ->values()
            ->all());

        $menus = [];

        foreach (self::DEFAULTS as $location => $defaults) {
            $links = [];

            foreach ($items as $item) {
                if ($item['location'] === $location) {
                    $links[] = ['label' => $item['label'], 'href' => $item['href'], 'external' => $item['external']];
                }
            }

            $menus[$location] = $links !== [] ? $links : array_map(fn (string $path): array => [
                'label' => self::INTERNAL_PAGES[$path],
                'href' => $path,
                'external' => false,
            ], $defaults);
        }

        return $menus;
    }

    /** True for an internal path such as /about or a full https:// address. */
    public static function isSafeUrl(string $url): bool
    {
        return (bool) preg_match('#^(/(?!/)[A-Za-z0-9\-._~/?=&%\#]*|https://[^\s<>"]+)$#', $url);
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
