<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Str;

/** The fixed site routes and safe starter layouts available to page editors. */
final class PageBlueprints
{
    public const FIXED_PAGES = [
        'home' => 'Homepage',
        'about' => 'About',
        'services' => 'Services',
        'insights' => 'Insights',
        'tools' => 'Accounting tools',
        'faq' => 'FAQ',
        'contact' => 'Contact',
        'book-consultation' => 'Book a consultation',
        'privacy' => 'Privacy notice',
        'terms' => 'Terms of use',
        'calculator-disclaimer' => 'Calculator disclaimer',
    ];

    public const STARTERS = [
        'blank' => 'Blank page — add your own blocks',
        'information' => 'Information page — introduction and key points',
        'services' => 'Services overview — introduction and service cards',
        'process' => 'How we work — introduction and steps',
    ];

    /** Copy for functional pages whose forms or listings are rendered by dedicated Vue components. */
    public const DEFAULT_COPY = [
        'services' => [
            'title' => 'Services',
            'hero_eyebrow' => 'Our services',
            'hero_line_one' => 'The right support for your numbers and your next decision.',
            'hero_description' => 'Explore our core areas of accounting and business support. Contact us to discuss the scope that fits your needs.',
            'seo_description' => 'Bookkeeping, financial reporting, tax filings and business solutions from ALCO Associate Solutions in Lagos.',
        ],
        'insights' => [
            'title' => 'Insights',
            'hero_eyebrow' => 'ALCO Insights',
            'hero_line_one' => 'Clearer thinking for your business.',
            'hero_description' => 'Practical articles on accounting, reporting, tax preparation and business finance. We publish only content that has completed ALCO’s editorial review.',
            'seo_description' => 'Practical accounting and business insights from ALCO Associate Solutions.',
        ],
        'tools' => [
            'title' => 'Free accounting calculators for business',
            'hero_eyebrow' => 'Accounting tools',
            'hero_line_one' => 'Free accounting calculators for clearer decisions.',
            'hero_description' => 'Explore VAT, withholding tax, pricing, profit, loans and cash-flow questions with practical tools. Enter your own figures and review the assumptions behind every result.',
            'seo_description' => 'Use ALCO’s free accounting calculators for VAT, withholding tax, profit margins, break-even, loans, depreciation, liquidity and cash collection.',
        ],
        'faq' => [
            'title' => 'Frequently asked questions',
            'hero_eyebrow' => 'FAQ',
            'hero_line_one' => 'Answers to common questions.',
            'hero_description' => 'If your question is not covered here, call or email us and we will help.',
            'seo_description' => 'Answers to common questions about working with ALCO Associate Solutions.',
        ],
        'contact' => [
            'title' => 'Contact us',
            'hero_eyebrow' => 'Contact',
            'hero_line_one' => 'Start a conversation with ALCO.',
            'hero_description' => 'Call or email us about bookkeeping, reporting, tax filings or a business question, or send a message below.',
            'seo_description' => 'Call, email or send a message to ALCO Associate Solutions about bookkeeping, reporting, tax filings or a business question.',
        ],
        'book-consultation' => [
            'title' => 'Book a consultation',
            'hero_eyebrow' => 'Book a consultation',
            'hero_line_one' => 'Let’s talk through what your business needs.',
            'hero_description' => 'Send a request below, or call or email us. We will confirm availability directly with you.',
            'seo_description' => 'Request a consultation with ALCO Associate Solutions. Choose a starting package or ask for a tailored engagement.',
        ],
    ];

    public const HOME_FIXED_COPY = [
        'services_eyebrow' => 'What we do',
        'services_title' => 'Good decisions begin with a clear picture.',
        'discover_eyebrow' => 'Explore more',
        'discover_title' => 'Useful guidance for the work ahead.',
        'discover_description' => 'Our Insights and Accounting Tools sections are being prepared to make everyday financial questions easier to explore.',
        'insights_card_title' => 'Insights',
        'insights_card_text' => 'Practical articles on accounting, reporting and tax topics.',
        'tools_card_title' => 'Accounting Tools',
        'tools_card_text' => 'Calculators to help you test and understand key figures.',
        'cta_eyebrow' => 'Let’s talk',
        'cta_title' => 'Ready to bring more clarity to your business?',
        'cta_description' => 'Tell us what you need help with and begin a conversation with ALCO.',
    ];

    public const ABOUT_FIXED_COPY = [
        'cta_title' => 'Let’s talk about your business.',
        'cta_description' => 'Tell us what you need help with and we will suggest a sensible starting point.',
    ];

    /** @return array<string, mixed> */
    public static function copyFor(string $slug): array
    {
        $defaults = self::DEFAULT_COPY[$slug] ?? [];
        $published = Page::published()
            ->where('slug', $slug)
            ->where('template_key', 'standard')
            ->first()?->publicContent() ?? [];

        return array_replace($defaults, $published);
    }

    public static function templateForSlug(string $slug): string
    {
        return match ($slug) {
            'home', 'about' => $slug,
            'privacy', 'terms', 'calculator-disclaimer' => 'legal',
            default => 'standard',
        };
    }

    public static function isCustom(string $slug): bool
    {
        return ! array_key_exists($slug, self::FIXED_PAGES);
    }

    public static function publicPath(string $slug): string
    {
        return match ($slug) {
            'home' => '/',
            'about', 'services', 'insights', 'tools', 'faq', 'contact', 'book-consultation', 'privacy', 'terms', 'calculator-disclaimer' => "/{$slug}",
            default => "/pages/{$slug}",
        };
    }

    /** @return list<array{type: string, data: array<string, mixed>}> */
    public static function starterBlocks(string $starter, string $title): array
    {
        $intro = [
            'type' => 'intro',
            'data' => [
                'eyebrow' => 'Overview',
                'title' => $title,
                'body' => 'Add a clear introduction to this page before publishing.',
            ],
        ];

        return match ($starter) {
            'information' => [$intro, [
                'type' => 'audience',
                'data' => [
                    'eyebrow' => 'Key points',
                    'title' => 'What visitors should know',
                    'body' => 'Replace this guidance with the details that matter to your visitors.',
                    'items' => [['label' => 'First point', 'detail' => 'Add a short explanation.']],
                ],
            ]],
            'services' => [$intro, [
                'type' => 'services',
                'data' => [
                    'eyebrow' => 'Our services',
                    'title' => 'How we can help',
                    'body' => 'Explore the services currently published by ALCO.',
                ],
            ]],
            'process' => [$intro, [
                'type' => 'process',
                'data' => [
                    'eyebrow' => 'Our approach',
                    'title' => 'How we work',
                    'steps' => [['title' => 'Start a conversation', 'detail' => 'Describe the help you need.']],
                ],
            ]],
            default => [],
        };
    }

    public static function slugFromTitle(string $title): string
    {
        return Str::slug($title);
    }
}
