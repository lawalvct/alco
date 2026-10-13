<?php

namespace Tests\Feature;

use App\Filament\Resources\EngagementPackages\Pages\CreateEngagementPackage;
use App\Filament\Resources\EngagementPackages\Pages\EditEngagementPackage;
use App\Models\EngagementPackage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class EngagementPackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_draft_packages_stay_private_until_published(): void
    {
        $essentials = EngagementPackage::factory()->create(['slug' => 'essentials', 'title' => 'Essentials']);

        $this->get('/services')->assertOk()->assertInertia(fn (Assert $page) => $page->where('packages', [])->etc());
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('packages', [])->etc());

        $essentials->publish();

        $this->get('/services')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('packages', 1)
            ->where('packages.0.slug', 'essentials')
            ->where('packages.0.name', 'Essentials')
            ->missing('packages.0.service_ids')
            ->etc());
    }

    public function test_inherited_packages_are_named_only_when_the_parent_is_published(): void
    {
        $essentials = EngagementPackage::factory()->create(['slug' => 'essentials', 'title' => 'Essentials', 'sort_order' => 1]);
        $growth = EngagementPackage::factory()->create([
            'slug' => 'growth',
            'title' => 'Growth',
            'sort_order' => 2,
            'parent_id' => $essentials->id,
            'draft_data' => [
                'summary' => 'Performance and financial health',
                'audience' => 'Scaling companies.',
                'inclusions' => [['label' => 'Cash-flow planning']],
            ],
        ]);
        $growth->publish();

        $this->get('/services')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('packages', 1)
            ->where('packages.0.includes', null)
            ->where('packages.0.inclusions', ['Cash-flow planning'])
            ->etc());

        $essentials->publish();

        $this->get('/services')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('packages', 2)
            ->where('packages.1.slug', 'growth')
            ->where('packages.1.includes.name', 'Essentials')
            ->etc());
    }

    public function test_consultation_preselects_only_published_selectable_packages(): void
    {
        EngagementPackage::factory()->create(['slug' => 'growth', 'title' => 'Growth'])->publish();
        EngagementPackage::factory()->create(['slug' => 'strategic', 'is_selectable' => false])->publish();
        EngagementPackage::factory()->create(['slug' => 'draft-only']);

        $this->get('/book-consultation?package=growth')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Consultation')
            ->where('selectedPackage.slug', 'growth')
            ->where('packageUnavailable', false)
            ->has('packages', 1)
            ->etc());

        foreach (['strategic', 'draft-only', 'unknown'] as $slug) {
            $this->get("/book-consultation?package={$slug}")->assertOk()->assertInertia(fn (Assert $page) => $page
                ->where('selectedPackage', null)
                ->where('packageUnavailable', true)
                ->etc());
        }

        $this->get('/book-consultation')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('selectedPackage', null)
            ->where('packageUnavailable', false)
            ->etc());
    }

    public function test_service_pages_list_the_published_packages_that_suit_them(): void
    {
        $bookkeeping = Service::factory()->create(['slug' => 'bookkeeping']);
        $reporting = Service::factory()->create(['slug' => 'financial-reporting']);
        $bookkeeping->publish();
        $reporting->publish();

        EngagementPackage::factory()->create([
            'slug' => 'essentials',
            'draft_data' => [
                'summary' => 'Bookkeeping, reconciliations and tax',
                'audience' => 'Small businesses.',
                'inclusions' => [['label' => 'Bookkeeping and accounting']],
                'service_ids' => [(string) $bookkeeping->id],
            ],
        ])->publish();

        $this->get('/services/bookkeeping')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('packages', 1)
            ->where('packages.0.slug', 'essentials')
            ->etc());

        $this->get('/services/financial-reporting')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('packages', [])
            ->etc());
    }

    public function test_admin_creates_a_draft_then_publishes_it_as_a_revision(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreateEngagementPackage::class)
            ->fillForm([
                'title' => 'Essentials',
                'slug' => 'essentials',
                'sort_order' => 1,
                'is_selectable' => true,
                'draft_data' => [
                    'summary' => 'Bookkeeping, reconciliations and tax',
                    'audience' => 'Startups, freelancers and small businesses.',
                    'inclusions' => [['label' => 'Bookkeeping and accounting']],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $package = EngagementPackage::where('slug', 'essentials')->firstOrFail();
        $this->assertNull($package->published_data);
        $this->assertTrue($package->hasUnpublishedChanges());

        Livewire::test(EditEngagementPackage::class, ['record' => $package->id])
            ->callAction('publishDraft')
            ->assertNotified();

        $package->refresh();
        $this->assertSame('published', $package->publication_status);
        $this->assertFalse($package->hasUnpublishedChanges());
        $this->assertSame(1, $package->revisions()->count());
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $package));
    }

    public function test_a_package_cannot_build_on_itself_through_another_package(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $essentials = EngagementPackage::factory()->create(['slug' => 'essentials']);
        $growth = EngagementPackage::factory()->create(['slug' => 'growth', 'parent_id' => $essentials->id]);

        Livewire::test(EditEngagementPackage::class, ['record' => $essentials->id])
            ->fillForm(['parent_id' => $growth->id])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);

        $this->assertNull($essentials->refresh()->parent_id);
    }
}
