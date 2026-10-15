<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_creates_blank_page_and_publishes_current_edits_in_one_action(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Business resources',
                'slug' => 'business-resources',
                'draft_data' => [
                    'hero_eyebrow' => 'Resources',
                    'hero_line_one' => 'Business resources',
                    'hero_description' => 'Guides and information for business owners.',
                    'seo_description' => 'Useful business resources from ALCO.',
                    'blocks' => [],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::where('slug', 'business-resources')->sole();
        $this->assertSame('standard', $page->template_key);
        $this->get('/pages/business-resources')->assertNotFound();

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->fillForm(['draft_data' => [
                'hero_eyebrow' => 'Resources',
                'hero_line_one' => 'Practical business resources',
                'hero_description' => 'Guides and information for business owners.',
                'seo_description' => 'Useful business resources from ALCO.',
                'blocks' => [[
                    'type' => 'rich_text',
                    'data' => [
                        'title' => 'Start here',
                        'body' => '<p>Useful content from ALCO.</p><script>alert(1)</script>',
                    ],
                ]],
            ]])
            ->callAction('saveAndPublish')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $page->refresh();
        $this->assertSame('Practical business resources', $page->published_data['hero_line_one']);
        $this->get('/pages/business-resources')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->component('Public/ContentPage')
            ->where('content.hero_line_one', 'Practical business resources')
            ->where('content.blocks.0.data.title', 'Start here')
            ->etc());
        $this->assertStringNotContainsString('<script>', $page->publicContent()['blocks'][0]['data']['body_html']);
        $this->get('/sitemap.xml')->assertSee('/pages/business-resources', false);
        $this->get('/pages/home')->assertNotFound();
    }

    public function test_saving_a_draft_keeps_the_live_page_unchanged_until_published(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $page = Page::factory()->create([
            'slug' => 'about',
            'template_key' => 'about',
            'draft_data' => [
                'hero_eyebrow' => 'About',
                'hero_line_one' => 'Original heading',
                'hero_description' => 'Original introduction.',
                'blocks' => [],
            ],
        ]);
        $page->publish();

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->fillForm(['draft_data.hero_line_one' => 'Draft heading'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/about')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('about.hero_line_one', 'Original heading')
            ->etc());

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->fillForm(['draft_data.hero_line_one' => 'Published heading'])
            ->callAction('saveAndPublish')
            ->assertHasNoFormErrors();

        $this->get('/about')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('about.hero_line_one', 'Published heading')
            ->etc());
    }

    public function test_contact_page_copy_updates_only_after_save_and_publish(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $page = Page::where('slug', 'contact')->sole();
        $original = $page->published_data['hero_line_one'];

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->fillForm(['draft_data.hero_line_one' => 'Talk to our accountants'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('pageCopy.hero_line_one', $original)
            ->etc());

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->callAction('saveAndPublish')
            ->assertHasNoFormErrors();

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('pageCopy.hero_line_one', 'Talk to our accountants')
            ->etc());
    }

    public function test_template_selector_prepares_editable_blocks_for_a_new_page(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Our approach', 'slug' => 'our-approach'])
            ->set('data.starter_template', 'process')
            ->assertSet('data.draft_data.blocks.0.type', 'intro')
            ->assertSet('data.draft_data.blocks.1.type', 'process');
    }

    public function test_all_page_block_bodies_keep_safe_rich_text_formatting(): void
    {
        $blocks = collect(['services', 'team', 'packages', 'intro', 'audience', 'rich_text'])
            ->map(fn (string $type): array => [
                'type' => $type,
                'data' => [
                    'eyebrow' => 'Overview',
                    'title' => ucfirst($type),
                    'body' => '<p><strong>Clear advice</strong> for owners.</p><script>alert(1)</script>',
                ],
            ])->all();

        $page = Page::factory()->create([
            'slug' => 'rich-page',
            'template_key' => 'standard',
            'draft_data' => [
                'hero_eyebrow' => 'Overview',
                'hero_line_one' => 'Rich page',
                'hero_description' => 'A page with formatted sections.',
                'blocks' => $blocks,
            ],
        ]);
        $page->publish();

        $publicBlocks = $page->publicContent()['blocks'];

        foreach ($publicBlocks as $block) {
            $this->assertStringContainsString('<strong>Clear advice</strong>', $block['data']['body_html']);
            $this->assertStringNotContainsString('<script>', $block['data']['body_html']);
            $this->assertArrayNotHasKey('body', $block['data']);
        }

        $this->get('/pages/rich-page')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('content.blocks.0.data.body_html', $publicBlocks[0]['data']['body_html'])
            ->etc());
    }

    public function test_admin_can_publish_testimonial_highlights_resource_links_and_cta_blocks(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $page = Page::factory()->create(['slug' => 'block-showcase', 'template_key' => 'standard']);

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->fillForm(['draft_data' => [
                'hero_eyebrow' => 'Showcase',
                'hero_line_one' => 'Block showcase',
                'hero_description' => 'Examples of reusable sections.',
                'blocks' => [
                    ['type' => 'testimonial', 'data' => [
                        'eyebrow' => 'Feedback', 'title' => 'A client perspective',
                        'quote' => 'A helpful test quote.', 'author' => 'Test Client', 'context' => 'Business owner',
                    ]],
                    ['type' => 'highlights', 'data' => [
                        'title' => 'Highlights',
                        'items' => [
                            ['label' => 'First', 'detail' => 'First detail.'],
                            ['label' => 'Second', 'detail' => 'Second detail.'],
                        ],
                    ]],
                    ['type' => 'resource_links', 'data' => [
                        'title' => 'Explore more',
                        'items' => [['label' => 'Insights', 'detail' => 'Read our articles.', 'href' => '/insights']],
                    ]],
                    ['type' => 'cta', 'data' => [
                        'eyebrow' => 'Let’s talk', 'title' => 'Ready to begin?',
                        'description' => 'Tell us what you need.',
                        'button_label' => 'Contact ALCO', 'button_href' => '/contact',
                    ]],
                ],
            ]])
            ->callAction('saveAndPublish')
            ->assertHasNoFormErrors();

        $this->get('/pages/block-showcase')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('content.blocks.0.data.quote', 'A helpful test quote.')
            ->where('content.blocks.1.data.items.1.label', 'Second')
            ->where('content.blocks.2.data.items.0.href', '/insights')
            ->where('content.blocks.3.data.button_href', '/contact')
            ->etc());
    }

    public function test_published_block_links_are_sanitized_even_if_content_bypasses_the_editor(): void
    {
        $page = Page::factory()->create([
            'slug' => 'unsafe-links',
            'template_key' => 'standard',
            'draft_data' => [
                'blocks' => [
                    ['type' => 'resource_links', 'data' => ['items' => [
                        ['label' => 'Safe', 'href' => '/contact'],
                        ['label' => 'Unsafe', 'href' => 'javascript:alert(1)'],
                    ]]],
                    ['type' => 'cta', 'data' => ['title' => 'Get in touch', 'button_href' => 'javascript:alert(1)']],
                ],
            ],
        ]);
        $page->publish();

        $blocks = $page->publicContent()['blocks'];
        $this->assertCount(1, $blocks[0]['data']['items']);
        $this->assertSame('/contact', $blocks[0]['data']['items'][0]['href']);
        $this->assertSame('/book-consultation', $blocks[1]['data']['button_href']);
    }

    public function test_admin_can_insert_the_lets_talk_cta_on_the_homepage(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $home = Page::factory()->create(['slug' => 'home', 'template_key' => 'home']);

        Livewire::test(EditPage::class, ['record' => $home->id])
            ->fillForm(['draft_data.blocks' => [[
                'type' => 'cta',
                'data' => [
                    'eyebrow' => 'Let’s talk',
                    'title' => 'Need help with your accounts?',
                    'description' => 'Start a conversation with ALCO.',
                    'button_label' => 'Contact us',
                    'button_href' => '/contact',
                ],
            ]]])
            ->callAction('saveAndPublish')
            ->assertHasNoFormErrors();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('home.blocks.0.type', 'cta')
            ->where('home.blocks.0.data.button_label', 'Contact us')
            ->etc());
    }
}
