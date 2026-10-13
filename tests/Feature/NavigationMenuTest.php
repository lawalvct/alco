<?php

namespace Tests\Feature;

use App\Filament\Resources\NavigationItems\Pages\CreateNavigationItem;
use App\Filament\Resources\NavigationItems\Pages\ListNavigationItems;
use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_menus_start_with_the_current_site_links(): void
    {
        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('navigation.header', 6)
            ->where('navigation.header.0', ['label' => 'Home', 'href' => '/', 'external' => false])
            ->where('navigation.header.5.label', 'Contact')
            ->where('navigation.footer.5', ['label' => 'FAQ', 'href' => '/faq', 'external' => false])
            ->etc());
    }

    public function test_admin_changes_appear_on_the_site_immediately(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/contact')->assertOk();

        Livewire::test(CreateNavigationItem::class)
            ->fillForm([
                'location' => 'header',
                'label' => 'Packages',
                'url' => '/services#packages',
                'is_visible' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        NavigationItem::where('label', 'Insights')->where('location', 'header')->sole()->update(['is_visible' => false]);

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('navigation.header', 6)
            ->where('navigation.header.4.label', 'Contact')
            ->where('navigation.header.5', ['label' => 'Packages', 'href' => '/services#packages', 'external' => false])
            ->etc());
    }

    public function test_reordering_updates_the_site_menu(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/contact')->assertOk();

        $order = NavigationItem::where('location', 'header')->orderByDesc('sort_order')->pluck('id')->all();

        Livewire::test(ListNavigationItems::class)
            ->call('reorderTable', array_map('strval', $order));

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('navigation.header.0.label', 'Contact')
            ->where('navigation.header.5.label', 'Home')
            ->etc());
    }

    public function test_external_links_are_flagged_and_an_empty_menu_falls_back(): void
    {
        NavigationItem::where('location', 'footer')->update(['is_visible' => false]);
        NavigationItem::factory()->create([
            'location' => 'header',
            'label' => 'LinkedIn',
            'url' => 'https://www.linkedin.com/company/example',
            'sort_order' => 99,
        ]);

        $this->get('/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('navigation.header.6.external', true)
            ->has('navigation.footer', 7)
            ->where('navigation.footer.0.label', 'Home')
            ->etc());
    }

    public function test_unsafe_links_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        foreach (['javascript:alert(1)', '//evil.example', 'http://insecure.example', 'about'] as $url) {
            Livewire::test(CreateNavigationItem::class)
                ->fillForm(['location' => 'footer', 'label' => 'Bad', 'url' => $url])
                ->call('create')
                ->assertHasFormErrors(['url']);
        }

        $this->assertDatabaseMissing('navigation_items', ['label' => 'Bad']);
    }
}
