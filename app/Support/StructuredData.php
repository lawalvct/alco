<?php

namespace App\Support;

/**
 * Collects schema.org JSON-LD for the current request. The business node
 * comes from Site settings, so it always matches the visible contact details.
 * Only facts that are already shown on the page are described: no ratings,
 * reviews, credentials or offers.
 */
final class StructuredData
{
    /** @var list<array<string, mixed>> */
    private array $nodes = [];

    /** Absolute URL for a site path, based on the configured production address. */
    public static function absolute(string $path = '/'): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /** @param  array<string, mixed>  $node */
    public function add(array $node): void
    {
        $this->nodes[] = $node;
    }

    /** @param  list<array{name: string, path: string}>  $trail  from the home page down to the current page */
    public function breadcrumbs(array $trail): void
    {
        $items = [];

        foreach ($trail as $position => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $crumb['name'],
                'item' => self::absolute($crumb['path']),
            ];
        }

        $this->add(['@type' => 'BreadcrumbList', 'itemListElement' => $items]);
    }

    /** JSON for the page head, safe to place inside a script tag. */
    public function json(bool $isHome): string
    {
        $graph = [$this->organization()];

        if ($isHome) {
            $graph[] = [
                '@type' => 'WebSite',
                '@id' => self::absolute('/#website'),
                'url' => self::absolute('/'),
                'name' => SiteSettings::publicPayload()['name'],
                'publisher' => ['@id' => self::absolute('/#organization')],
            ];
        }

        $graph = [...$graph, ...$this->nodes];

        return (string) json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        );
    }

    /** @return array<string, mixed> */
    private function organization(): array
    {
        $site = SiteSettings::publicPayload();
        $address = $site['address'];

        $node = [
            '@type' => 'AccountingService',
            '@id' => self::absolute('/#organization'),
            'name' => $site['name'],
            'url' => self::absolute('/'),
            'description' => $site['category'] !== '' ? $site['category'] : null,
            'email' => $site['emails'][0] ?? null,
            'telephone' => $site['phones'][0]['tel'] ?? null,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address['street'],
                'addressLocality' => $address['locality'],
                'addressRegion' => $address['region'],
                'addressCountry' => strcasecmp($address['country'], 'Nigeria') === 0 ? 'NG' : $address['country'],
            ],
            // Only profile links that staff have entered and verified.
            'sameAs' => array_column($site['social'], 'url') ?: null,
        ];

        return array_filter($node, fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
