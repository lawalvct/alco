<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Seeder;
use RuntimeException;

class LocalContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Provisional content can only be seeded in the local environment.');
        }

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
                                'items' => [
                                    ['question' => 'Where is ALCO based?', 'answer' => '18, Lagos Street, Ebute-Metta, Lagos State, Nigeria.'],
                                    ['question' => 'Which services can I ask about?', 'answer' => 'Bookkeeping, financial reporting, tax filings and business solutions are the four core areas listed by ALCO.'],
                                    ['question' => 'How do I request a consultation?', 'answer' => 'Call either listed phone number or email info@alcosolutions.ng. Availability is confirmed directly.'],
                                ],
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
