<?php

namespace Database\Seeders;

use App\Models\EngagementPackage;
use App\Models\Faq;
use App\Models\HomepageHeroSlide;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Service;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Provisional content can only be seeded in the local environment.');
        }

        $faqIds = $this->seedFaqs();

        $home = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Homepage',
                'template_key' => 'home',
                'draft_data' => [
                    'title' => 'Homepage',
                    'hero_eyebrow' => 'ALCO Associate Solutions',
                    'hero_line_one' => 'Your numbers, in order.',
                    'hero_line_two' => 'Your business,',
                    'hero_accent' => 'moving forward.',
                    'hero_description' => 'Professional accounting, clear reporting and practical business support.',
                    'seo_description' => 'ALCO Associate Solutions offers bookkeeping, financial reporting, tax filings and business solutions in Lagos, Nigeria.',
                    'blocks' => [
                        [
                            'type' => 'packages',
                            'data' => [
                                'eyebrow' => 'Engagement packages',
                                'title' => 'A clear starting point for ongoing support.',
                                'body' => 'Choose the level of support that fits today. We confirm suitability, scope and terms with you before any work begins.',
                            ],
                        ],
                        [
                            'type' => 'intro',
                            'data' => [
                                'eyebrow' => 'A clearer starting point',
                                'title' => 'Accounting support grounded in your business needs.',
                                'body' => 'ALCO Associate Solutions is an Accounting and Auditing Consultancy in Ebute-Metta, Lagos. Our stated areas of focus are bookkeeping, financial reporting, tax filings and business solutions.',
                            ],
                        ],
                        [
                            'type' => 'audience',
                            'data' => [
                                'eyebrow' => 'Who we can help',
                                'title' => 'Bring your questions. We will discuss the right scope.',
                                'body' => 'Good financial information helps people make better decisions. Tell us what you are trying to understand or improve, and we can discuss whether our services fit.',
                                'items' => [
                                    ['label' => 'Business owners', 'detail' => 'Organise records and understand the figures behind daily decisions.'],
                                    ['label' => 'Growing teams', 'detail' => 'Discuss reporting and processes as operations become more complex.'],
                                    ['label' => 'Professionals', 'detail' => 'Explore practical support for your accounting and filing questions.'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'process',
                            'data' => [
                                'eyebrow' => 'Getting started',
                                'title' => 'A straightforward conversation comes first.',
                                'steps' => [
                                    ['title' => 'Tell us what you need', 'detail' => 'Share the service or financial question you would like to discuss.'],
                                    ['title' => 'Explore the scope', 'detail' => 'We talk through your goals, available information and possible next steps.'],
                                    ['title' => 'Agree the way forward', 'detail' => 'If there is a fit, we can define the work before it begins.'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'faq',
                            'data' => [
                                'eyebrow' => 'Common questions',
                                'title' => 'Start with the essentials.',
                                'faq_ids' => $faqIds,
                            ],
                        ],
                    ],
                ],
            ],
        );

        if ($home->wasRecentlyCreated) {
            $home->publish();
        }

        foreach ($this->services() as $position => $item) {
            $service = Service::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'sort_order' => $position + 1,
                    'draft_data' => $item,
                ],
            );

            if ($service->wasRecentlyCreated) {
                $service->publish();
            }
        }

        $this->seedPackages();
        $this->seedTeam();
        $this->seedHeroSlides();
    }

    /**
     * Client-supplied package copy, seeded as unpublished drafts: ALCO must
     * approve wording and terms before publishing (development plan §5).
     */
    private function seedPackages(): void
    {
        $serviceIds = Service::query()->pluck('id', 'slug');
        $parent = null;

        foreach ($this->packages() as $position => $item) {
            $package = EngagementPackage::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'parent_id' => $parent?->id,
                    'sort_order' => $position + 1,
                    'is_selectable' => true,
                    'draft_data' => [
                        'summary' => $item['summary'],
                        'audience' => $item['audience'],
                        'inclusions' => array_map(fn (string $label): array => ['label' => $label], $item['inclusions']),
                        'service_ids' => $serviceIds->only($item['services'])->values()->all(),
                    ],
                ],
            );

            $parent = $package;
        }
    }

    /** @return list<string> FAQ ids in homepage order */
    private function seedFaqs(): array
    {
        $faqs = [
            ['question' => 'Where is ALCO based?', 'answer' => '18, Lagos Street, Ebute-Metta, Lagos State, Nigeria.'],
            ['question' => 'Which services can I ask about?', 'answer' => 'Bookkeeping, financial reporting, tax filings and business solutions are the four core areas listed by ALCO.'],
            ['question' => 'How do I request a consultation?', 'answer' => 'Call either listed phone number or email info@alcosolutions.ng. Availability is confirmed directly.'],
        ];

        $ids = [];

        foreach ($faqs as $position => $faq) {
            $ids[] = (string) Faq::firstOrCreate(
                ['question' => $faq['question']],
                ['answer' => $faq['answer'], 'group' => 'General', 'sort_order' => $position + 1, 'is_published' => true],
            )->id;
        }

        return $ids;
    }

    /** Names are owner-supplied; roles, biographies and photos await approval. */
    private function seedTeam(): void
    {
        foreach (['Abdulsalam Latifah Damilola', 'Abdulazeez Azeez Olaitan'] as $position => $name) {
            TeamMember::firstOrCreate(
                ['name' => $name],
                ['sort_order' => $position + 1, 'is_published' => true],
            );
        }
    }

    /**
     * Registers the sample banner images as hidden slides. Their usage rights
     * are unconfirmed, so they stay hidden until someone confirms rights in
     * the media library and turns the slide on.
     */
    private function seedHeroSlides(): void
    {
        $images = [
            ['file' => 'hero-slider1.png', 'title' => 'Homepage slider – team meeting', 'alt' => 'Four professionals talking around a boardroom table, with a city skyline and a growth chart on a screen behind them.'],
            ['file' => 'hero-slider2.png', 'title' => 'Homepage slider – reviewing reports', 'alt' => 'A professional reviewing financial documents at a desk with a calculator and laptop, with a city skyline and bridge through the window.'],
        ];

        foreach ($images as $position => $image) {
            $source = public_path('images/'.$image['file']);
            $path = 'media/sample-'.$image['file'];

            if (! is_file($source) || MediaAsset::where('path', $path)->exists()) {
                continue;
            }

            Storage::disk('public')->put($path, (string) file_get_contents($source));

            $asset = MediaAsset::create([
                'title' => $image['title'],
                'path' => $path,
                'original_name' => $image['file'],
                'alt_text' => $image['alt'],
                'usage_rights' => 'Supplied for development. Confirm the source and licence before publishing.',
                'rights_confirmed' => false,
            ]);

            HomepageHeroSlide::create([
                'media_asset_id' => $asset->id,
                'sort_order' => $position + 1,
                'is_visible' => false,
            ]);
        }
    }

    /** @return list<array{slug: string, title: string, summary: string, audience: string, inclusions: list<string>, services: list<string>}> */
    private function packages(): array
    {
        return [
            [
                'slug' => 'essentials',
                'title' => 'Essentials',
                'summary' => 'Bookkeeping, reconciliations and tax',
                'audience' => 'Startups, freelancers and small businesses that need clean records, accurate reporting and peace of mind at tax time.',
                'inclusions' => ['Bookkeeping and accounting', 'Reconciliation and account management', 'Tax preparation and filing'],
                'services' => ['bookkeeping', 'tax-filings'],
            ],
            [
                'slug' => 'growth',
                'title' => 'Growth',
                'summary' => 'Performance and financial health',
                'audience' => 'Scaling companies seeking visibility into profit leaks, margins and operational performance.',
                'inclusions' => ['Financial performance analysis', 'Financial health and profitability reviews', 'Cash-flow planning'],
                'services' => ['financial-reporting', 'business-solutions'],
            ],
            [
                'slug' => 'strategic',
                'title' => 'Strategic',
                'summary' => 'Forecasting and decision support',
                'audience' => 'Mature mid-market companies navigating complex growth and structural shifts that need forward planning and high-level financial guidance.',
                'inclusions' => ['Budgeting and forecasting', 'Advanced scenario planning', 'Business decision support'],
                'services' => ['business-solutions'],
            ],
        ];
    }

    /** @return list<array{slug: string, title: string, summary: string, body: string, audience: string, deliverables: list<array{label: string}>}> */
    private function services(): array
    {
        return [
            [
                'slug' => 'bookkeeping',
                'title' => 'Bookkeeping',
                'summary' => 'Keep everyday records organised and ready for clearer decisions.',
                'body' => 'Clear records make it easier to understand day-to-day activity and prepare useful reports. We can discuss the bookkeeping support that fits the records and processes your business already uses.',
                'audience' => 'For businesses that want more organised financial records.',
                'deliverables' => [
                    ['label' => 'Transaction record organisation'],
                    ['label' => 'Account reconciliation support'],
                    ['label' => 'Reporting-ready information'],
                ],
            ],
            [
                'slug' => 'financial-reporting',
                'title' => 'Financial Reporting',
                'summary' => 'Understand performance through timely, useful financial reports.',
                'body' => 'Financial reports can turn records into a clearer picture of performance. Tell us what you need to understand, and we can discuss the reporting scope and information required.',
                'audience' => 'For decision makers who need a clearer view of financial performance.',
                'deliverables' => [
                    ['label' => 'Reporting needs discussion'],
                    ['label' => 'Management information preparation'],
                    ['label' => 'Review of key figures'],
                ],
            ],
            [
                'slug' => 'tax-filings',
                'title' => 'Tax Filings',
                'summary' => 'Prepare filing information and navigate tax obligations with support.',
                'body' => 'Tax filing needs depend on your circumstances and the applicable rules. We can discuss the records and preparation steps relevant to your situation before agreeing a service scope.',
                'audience' => 'For businesses seeking help with filing preparation and records.',
                'deliverables' => [
                    ['label' => 'Filing requirements discussion'],
                    ['label' => 'Supporting-records review'],
                    ['label' => 'Preparation scope planning'],
                ],
            ],
            [
                'slug' => 'business-solutions',
                'title' => 'Business Solutions',
                'summary' => 'Bring financial clarity to planning and everyday business choices.',
                'body' => 'Business decisions benefit from sound information. We can explore the financial questions behind your plans and discuss practical support suited to your organisation.',
                'audience' => 'For owners and teams considering their next business decision.',
                'deliverables' => [
                    ['label' => 'Business question review'],
                    ['label' => 'Financial information discussion'],
                    ['label' => 'Agreed next-step recommendations'],
                ],
            ],
        ];
    }
}
