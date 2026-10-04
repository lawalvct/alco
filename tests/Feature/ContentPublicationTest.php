<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class ContentPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_page_draft_is_not_public_and_publishing_uses_a_snapshot(): void
    {
        $page = Page::factory()->create([
            'slug' => 'home',
            'title' => 'Homepage',
            'draft_data' => [
                'hero_eyebrow' => 'ALCO',
                'hero_line_one' => 'First draft heading',
                'blocks' => [],
            ],
        ]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->component('Welcome')
            ->where('home', null)
            ->etc());

        $page->publish();
        $this->get('/')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->component('Welcome')
            ->where('home.hero_line_one', 'First draft heading')
            ->etc());

        $page->update(['draft_data' => [
            'hero_eyebrow' => 'ALCO',
            'hero_line_one' => 'Second draft heading',
            'blocks' => [],
        ]]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->component('Welcome')
            ->where('home.hero_line_one', 'First draft heading')
            ->etc());

        $page->publish();
        $this->assertSame(2, $page->revisions()->count());
        $this->get('/')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->component('Welcome')
            ->where('home.hero_line_one', 'Second draft heading')
            ->etc());
    }

    public function test_only_current_published_services_have_detail_pages(): void
    {
        $service = Service::factory()->create([
            'slug' => 'bookkeeping',
            'title' => 'Bookkeeping',
            'draft_data' => [
                'summary' => 'Organised records',
                'body' => 'Useful bookkeeping support.',
                'audience' => 'Business owners',
                'seo_description' => 'Bookkeeping support in Lagos.',
                'deliverables' => [['label' => 'Reconciliation']],
            ],
        ]);

        $this->get('/services/bookkeeping')->assertNotFound();
        $service->publish(now()->addDay());
        $this->get('/services/bookkeeping')->assertNotFound();

        $service->publish();
        $this->get('/services/bookkeeping')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->component('Public/Service')
            ->where('service.title', 'Bookkeeping')
            ->where('service.seo_description', 'Bookkeeping support in Lagos.')
            ->where('service.deliverables.0.label', 'Reconciliation')
            ->etc());

        $service->update(['draft_data' => ['summary' => 'Changed draft', 'body' => 'Changed body']]);
        $this->get('/services/bookkeeping')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('service.summary', 'Organised records')
            ->etc());

        $service->forceFill(['publication_status' => 'archived'])->save();
        $this->get('/services/bookkeeping')->assertNotFound();
    }

    public function test_cms_requires_an_admin_user(): void
    {
        $regularUser = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $service = Service::factory()->create();

        $this->assertFalse(Gate::forUser($regularUser)->allows('publish', $service));
        $this->assertTrue(Gate::forUser($admin)->allows('publish', $service));

        $this->actingAs($regularUser)->get('/admin/services')->assertForbidden();
        $this->actingAs($admin)->get('/admin/services')->assertOk();
        $this->actingAs($admin)->get('/admin/services/create')->assertOk();
        $this->actingAs($admin)->get('/admin/pages/create')->assertOk();
    }

    public function test_admin_can_save_service_draft_without_publishing_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreateService::class)
            ->fillForm([
                'title' => 'Bookkeeping',
                'slug' => 'bookkeeping',
                'sort_order' => 1,
                'draft_data' => [
                    'summary' => 'Records you can understand.',
                    'body' => 'A draft description of the service.',
                    'audience' => 'Business owners',
                    'deliverables' => [['label' => 'Record organisation']],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::where('slug', 'bookkeeping')->firstOrFail();
        $this->assertSame('Records you can understand.', $service->draft_data['summary']);
        $this->assertNull($service->published_data);
        $this->get('/services/bookkeeping')->assertNotFound();
    }

    public function test_admin_publish_action_releases_a_snapshot_only_after_review(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $service = Service::factory()->create([
            'slug' => 'bookkeeping',
            'title' => 'Bookkeeping',
            'draft_data' => [
                'summary' => 'First approved summary',
                'body' => 'First approved body',
                'audience' => 'Business owners',
                'deliverables' => [],
            ],
        ]);
        $this->actingAs($admin);

        Livewire::test(EditService::class, ['record' => $service->id])
            ->callAction('publishDraft')
            ->assertNotified();

        $this->get('/services/bookkeeping')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('service.summary', 'First approved summary')
            ->etc());

        Livewire::test(EditService::class, ['record' => $service->id])
            ->fillForm([
                'draft_data' => [
                    'summary' => 'Revised, still private',
                    'body' => 'Revised body',
                    'audience' => 'Business owners',
                    'deliverables' => [],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/services/bookkeeping')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('service.summary', 'First approved summary')
            ->etc());
    }

    public function test_admin_can_create_and_publish_homepage_blocks(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Homepage',
                'slug' => 'home',
                'template_key' => 'home',
                'draft_data' => [
                    'hero_eyebrow' => 'ALCO',
                    'hero_line_one' => 'Clearer records',
                    'hero_line_two' => 'Better decisions,',
                    'hero_accent' => 'moving forward.',
                    'hero_description' => 'Accounting and business support.',
                    'seo_description' => 'Accounting services in Lagos.',
                    'blocks' => [[
                        'type' => 'intro',
                        'data' => [
                            'eyebrow' => 'About us',
                            'title' => 'Lagos accounting support',
                            'body' => 'ALCO is based in Ebute-Metta.',
                        ],
                    ]],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::where('slug', 'home')->firstOrFail();
        $this->assertSame('Lagos accounting support', $page->draft_data['blocks'][0]['data']['title']);
        $this->assertNull($page->published_data);

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->callAction('publishDraft')
            ->assertNotified();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $response) => $response
            ->where('home.blocks.0.data.title', 'Lagos accounting support')
            ->etc());
    }
}
