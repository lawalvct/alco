<?php

namespace Tests\Feature;

use App\Filament\Resources\HomepageHeroSlides\Pages\CreateHomepageHeroSlide;
use App\Filament\Resources\HomepageHeroSlides\Pages\EditHomepageHeroSlide;
use App\Filament\Resources\HomepageHeroSlides\Pages\ListHomepageHeroSlides;
use App\Models\HomepageHeroSlide;
use App\Models\MediaAsset;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class HomepageHeroSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_homepage_falls_back_to_the_static_banner_when_no_slide_is_visible(): void
    {
        HomepageHeroSlide::factory()->create(['is_visible' => false]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('heroSlides', [])
            ->etc());
    }

    public function test_homepage_receives_only_visible_slides_in_order(): void
    {
        $second = HomepageHeroSlide::factory()->visible()->create([
            'sort_order' => 2,
            'is_decorative' => true,
            'focal_x' => 30,
            'focal_y' => 60,
        ]);
        $first = HomepageHeroSlide::factory()->visible()->create([
            'sort_order' => 1,
            'mobile_media_asset_id' => MediaAsset::factory()->dimensions(1200, 840),
            'mobile_focal_x' => 70,
        ]);
        HomepageHeroSlide::factory()->create(['sort_order' => 0]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('heroSlides', 2)
            ->where('heroSlides.0.id', $first->id)
            ->where('heroSlides.0.alt', $first->mediaAsset->alt_text)
            ->where('heroSlides.0.mobile.width', 1200)
            ->where('heroSlides.0.mobile_position', '70% 50%')
            ->where('heroSlides.1.id', $second->id)
            ->where('heroSlides.1.alt', '')
            ->where('heroSlides.1.position', '30% 60%')
            ->where('heroSlides.1.mobile', null)
            ->etc());
    }

    public function test_admin_can_add_a_slide_that_goes_straight_to_the_homepage(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $asset = MediaAsset::factory()->rightsUnconfirmed()->create(['alt_text' => null]);

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => $asset->id,
                'image_alt_text' => 'Advisers reviewing a report.',
                'image_rights_confirmed' => true,
                'focal_x' => 40,
                'focal_y' => 50,
                'is_visible' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $slide = HomepageHeroSlide::sole();
        $this->assertTrue($slide->is_visible);
        $this->assertSame(1, $slide->sort_order);
        $this->assertSame($admin->id, $slide->created_by);

        $asset->refresh();
        $this->assertTrue($asset->rights_confirmed);
        $this->assertSame('Advisers reviewing a report.', $asset->alt_text);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('heroSlides', 1)
            ->where('heroSlides.0.alt', 'Advisers reviewing a report.')
            ->where('heroSlides.0.position', '40% 50%')
            ->etc());
    }

    public function test_small_or_badly_shaped_images_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm(['media_asset_id' => MediaAsset::factory()->dimensions(1200, 400)->create()->id])
            ->call('create')
            ->assertHasFormErrors(['media_asset_id']);

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm(['media_asset_id' => MediaAsset::factory()->dimensions(1920, 1080)->create()->id])
            ->call('create')
            ->assertHasFormErrors(['media_asset_id']);

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => MediaAsset::factory()->create()->id,
                'mobile_media_asset_id' => MediaAsset::factory()->dimensions(2560, 820)->create()->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['mobile_media_asset_id']);

        $this->assertDatabaseCount('homepage_hero_slides', 0);
    }

    public function test_a_slide_needs_confirmed_rights_and_alt_text_to_be_shown(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => MediaAsset::factory()->rightsUnconfirmed()->create()->id,
                'image_alt_text' => 'A meeting.',
                'image_rights_confirmed' => false,
                'is_visible' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['is_visible']);

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => MediaAsset::factory()->create()->id,
                'image_alt_text' => '',
                'is_decorative' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['image_alt_text']);

        $this->assertDatabaseCount('homepage_hero_slides', 0);

        $this->expectException(ValidationException::class);
        HomepageHeroSlide::factory()->visible()->create([
            'media_asset_id' => MediaAsset::factory()->rightsUnconfirmed(),
        ]);
    }

    public function test_rights_can_be_confirmed_from_the_slide_form(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $main = MediaAsset::factory()->rightsUnconfirmed()->create();
        $phone = MediaAsset::factory()->rightsUnconfirmed()->dimensions(1200, 840)->create();
        $slide = HomepageHeroSlide::factory()->create([
            'media_asset_id' => $main->id,
            'mobile_media_asset_id' => $phone->id,
        ]);

        Livewire::test(EditHomepageHeroSlide::class, ['record' => $slide->id])
            ->assertSchemaStateSet([
                'image_rights_confirmed' => false,
                'image_alt_text' => $main->alt_text,
            ])
            ->fillForm(['image_rights_confirmed' => true, 'is_visible' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($slide->refresh()->is_visible);
        $this->assertTrue($main->refresh()->rights_confirmed);
        $this->assertTrue($phone->refresh()->rights_confirmed);
    }

    public function test_slides_can_be_shown_and_hidden_from_the_list(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $asset = MediaAsset::factory()->rightsUnconfirmed()->create(['alt_text' => null]);
        $slide = HomepageHeroSlide::factory()->create(['media_asset_id' => $asset->id]);

        Livewire::test(ListHomepageHeroSlides::class)
            ->callAction(TestAction::make('show')->table($slide), ['confirm_rights' => false, 'alt_text' => 'Team meeting'])
            ->assertHasFormErrors(['confirm_rights']);

        $this->assertFalse($slide->refresh()->is_visible);

        Livewire::test(ListHomepageHeroSlides::class)
            ->callAction(TestAction::make('show')->table($slide), ['confirm_rights' => true, 'alt_text' => 'Team meeting'])
            ->assertHasNoFormErrors()
            ->assertNotified('Slide is now on the homepage');

        $this->assertTrue($slide->refresh()->is_visible);
        $this->assertTrue($asset->refresh()->rights_confirmed);
        $this->assertSame('Team meeting', $asset->alt_text);

        Livewire::test(ListHomepageHeroSlides::class)
            ->callAction(TestAction::make('hide')->table($slide));

        $this->assertFalse($slide->refresh()->is_visible);
    }

    public function test_no_more_than_ten_slides_can_be_visible(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        HomepageHeroSlide::factory()->visible()->count(10)->create();

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => MediaAsset::factory()->create()->id,
                'image_alt_text' => 'A meeting.',
                'image_rights_confirmed' => true,
                'is_visible' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['is_visible']);

        $eleventh = HomepageHeroSlide::factory()->create();

        Livewire::test(ListHomepageHeroSlides::class)
            ->callAction(TestAction::make('show')->table($eleventh))
            ->assertNotified('Slide not shown');

        $this->assertFalse($eleventh->refresh()->is_visible);

        $this->expectException(ValidationException::class);
        HomepageHeroSlide::factory()->visible()->create();
    }
}
