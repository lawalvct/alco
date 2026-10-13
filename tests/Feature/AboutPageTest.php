<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_about_page_works_before_any_content_is_published(): void
    {
        $service = Service::factory()->create(['slug' => 'bookkeeping', 'title' => 'Bookkeeping']);
        $service->publish();
        TeamMember::factory()->create(['name' => 'Abdulsalam Latifah Damilola']);

        $this->get('/about')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/About')
            ->where('about', null)
            ->where('services.0.title', 'Bookkeeping')
            ->where('team.0.name', 'Abdulsalam Latifah Damilola')
            ->etc());
    }

    public function test_published_about_content_and_blocks_reach_the_page(): void
    {
        $about = Page::factory()->create([
            'slug' => 'about',
            'title' => 'About',
            'template_key' => 'about',
            'draft_data' => [
                'hero_eyebrow' => 'About ALCO',
                'hero_line_one' => 'Accounting support with a clear purpose.',
                'hero_description' => 'Based in Ebute-Metta, Lagos.',
                'blocks' => [
                    ['type' => 'intro', 'data' => ['eyebrow' => 'Who we are', 'title' => 'Our story', 'body' => 'Story text.']],
                    ['type' => 'team', 'data' => ['eyebrow' => 'Leadership', 'title' => 'The people behind ALCO.']],
                ],
            ],
        ]);
        $about->publish();

        $about->update(['draft_data' => [...$about->draft_data, 'hero_line_one' => 'Unpublished draft heading']]);

        $this->get('/about')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('about.hero_line_one', 'Accounting support with a clear purpose.')
            ->where('about.blocks.0.type', 'intro')
            ->where('about.blocks.1.type', 'team')
            ->etc());
    }

    public function test_admin_can_create_and_publish_the_about_page_without_homepage_fields(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreatePage::class)
            ->fillForm(['slug' => 'about'])
            ->assertSchemaStateSet(['template_key' => 'about'])
            ->fillForm([
                'title' => 'About',
                'draft_data' => [
                    'hero_eyebrow' => 'About ALCO',
                    'hero_line_one' => 'Accounting support with a clear purpose.',
                    'hero_description' => 'Based in Ebute-Metta, Lagos.',
                    'blocks' => [
                        ['type' => 'services', 'data' => ['eyebrow' => 'What we do', 'title' => 'Four areas of focus.']],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $about = Page::where('slug', 'about')->sole();
        $this->assertSame('about', $about->template_key);
        $this->assertArrayNotHasKey('hero_line_two', $about->draft_data);

        Livewire::test(EditPage::class, ['record' => $about->id])
            ->callAction('publishDraft')
            ->assertNotified();

        $this->get('/about')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('about.blocks.0.type', 'services')
            ->etc());
    }

    public function test_each_page_can_only_be_created_once(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Page::factory()->create(['slug' => 'about', 'template_key' => 'about']);

        Livewire::test(CreatePage::class)
            ->fillForm(['slug' => 'about', 'title' => 'Second about'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }
}
