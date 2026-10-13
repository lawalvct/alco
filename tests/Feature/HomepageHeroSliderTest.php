<?php

namespace Tests\Feature;

use App\Filament\Resources\HomepageHeroSlides\Pages\CreateHomepageHeroSlide;
use App\Models\HomepageHeroSlide;
use App\Models\MediaAsset;
use App\Models\User;
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

    public function test_admin_can_add_a_slide_that_meets_the_image_rules(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $asset = MediaAsset::factory()->create();

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => $asset->id,
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

    public function test_images_without_confirmed_rights_or_alt_text_cannot_be_shown(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => MediaAsset::factory()->rightsUnconfirmed()->create()->id,
                'is_visible' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['is_visible']);

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => MediaAsset::factory()->create(['alt_text' => null])->id,
                'is_decorative' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['is_decorative']);

        $this->expectException(ValidationException::class);
        HomepageHeroSlide::factory()->visible()->create([
            'media_asset_id' => MediaAsset::factory()->rightsUnconfirmed(),
        ]);
    }

    public function test_no_more_than_ten_slides_can_be_visible(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        HomepageHeroSlide::factory()->visible()->count(10)->create();

        Livewire::test(CreateHomepageHeroSlide::class)
            ->fillForm([
                'media_asset_id' => MediaAsset::factory()->create()->id,
                'is_visible' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['is_visible']);

        $this->expectException(ValidationException::class);
        HomepageHeroSlide::factory()->visible()->create();
    }
}
