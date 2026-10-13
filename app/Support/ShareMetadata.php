<?php

namespace App\Support;

/** Social previews are rendered by Blade so link crawlers see them without JavaScript or Inertia SSR. */
final class ShareMetadata
{
    /** @param array<string, mixed> $page
     * @return array{title: string, description: string, url: string, image: string, imageAlt: string, type: string}
     */
    public static function forPage(array $page): array
    {
        $site = SiteSettings::publicPayload();
        $props = is_array($page['props'] ?? null) ? $page['props'] : [];
        $component = (string) ($page['component'] ?? '');
        $name = $site['name'];
        $defaultDescription = trim(str_replace(' | ', ', ', $site['positioning']).'. Based in Lagos, Nigeria.');

        [$pageTitle, $description] = match ($component) {
            'Welcome' => [$name, self::first($props['home']['seo_description'] ?? null, $props['home']['hero_description'] ?? null, $defaultDescription)],
            'Public/About' => ['About us', self::first($props['about']['seo_description'] ?? null, $props['about']['hero_description'] ?? null, $defaultDescription)],
            'Public/Section' => ['Accounting and business services', 'Bookkeeping, financial reporting, tax filings and business support from ALCO Associate Solutions in Lagos.'],
            'Public/Service' => [self::first($props['service']['title'] ?? null, 'Our services'), self::first($props['service']['seo_description'] ?? null, $props['service']['summary'] ?? null, $defaultDescription)],
            'Public/InsightsIndex' => [self::first($props['selectedCategory']['name'] ?? null, 'Insights'), self::first($props['selectedCategory']['seo_description'] ?? null, $props['selectedCategory']['description'] ?? null, 'Practical accounting, tax and business insights from ALCO Associate Solutions.')],
            'Public/TopicHub' => [self::first($props['topic']['title'] ?? null, 'Insights'), self::first($props['topic']['seo_description'] ?? null, $props['topic']['intro'] ?? null, $defaultDescription)],
            'Public/Article' => [self::first($props['post']['title'] ?? null, 'Insights'), self::first($props['post']['seo_description'] ?? null, $props['post']['excerpt'] ?? null, $defaultDescription)],
            'Public/ToolsIndex' => ['Free accounting calculators for business', 'Explore free VAT, withholding tax, margin, loan, cash flow and other business calculators from ALCO Associate Solutions.'],
            'Public/Calculator' => [self::first($props['tool']['seoTitle'] ?? null, $props['tool']['title'] ?? null, 'Accounting calculator'), self::first($props['tool']['seoDescription'] ?? null, $props['tool']['summary'] ?? null, $defaultDescription)],
            'Public/Faq' => ['Frequently asked questions', 'Answers to common questions about ALCO Associate Solutions and our accounting services.'],
            'Public/Contact' => ['Contact us', 'Contact ALCO Associate Solutions for bookkeeping, financial reporting, tax filings and business support in Lagos.'],
            'Public/Consultation' => ['Book a consultation', 'Discuss your accounting and business support needs with ALCO Associate Solutions.'],
            'Public/Legal' => [self::first($props['legal']['title'] ?? null, 'Legal information'), self::first($props['legal']['seo_description'] ?? null, $props['legal']['intro'] ?? null, $defaultDescription)],
            default => [$name, $defaultDescription],
        };

        $url = $props['canonical'] ?? null;

        return [
            'title' => $pageTitle === $name ? $name : "{$pageTitle} | {$name}",
            'description' => $description,
            'url' => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) ? $url : StructuredData::absolute('/'),
            'image' => StructuredData::absolute('/images/alco-social-share.png'),
            'imageAlt' => 'ALCO Associate Solutions — professional accounting services in Lagos, Nigeria',
            'type' => $component === 'Public/Article' ? 'article' : 'website',
        ];
    }

    private static function first(mixed ...$values): string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }
}
