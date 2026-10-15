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
}
