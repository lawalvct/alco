<?php

namespace Tests\Feature;

use App\Filament\Resources\MediaAssets\Pages\CreateMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\EditMediaAsset;
use App\Models\HomepageHeroSlide;
use App\Models\MediaAsset;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_uploaded_images_record_their_dimensions_and_uploader(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreateMediaAsset::class)
            ->fillForm([
                'path' => UploadedFile::fake()->image('banner.jpg', 2560, 820),
                'title' => 'Office banner',
                'alt_text' => 'An accountant reviewing reports.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $asset = MediaAsset::sole();
        $this->assertSame(2560, $asset->width);
        $this->assertSame(820, $asset->height);
        $this->assertSame('image/jpeg', $asset->mime_type);
        $this->assertSame('banner.jpg', $asset->original_name);
        $this->assertSame($admin->id, $asset->uploaded_by);
        $this->assertFalse($asset->rights_confirmed);
        $this->assertStringStartsWith('media/', $asset->path);
        Storage::disk('public')->assertExists($asset->path);
    }

    public function test_svg_and_other_non_photo_files_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreateMediaAsset::class)
            ->fillForm([
                'path' => UploadedFile::fake()->create('logo.svg', 4, 'image/svg+xml'),
                'title' => 'Logo',
            ])
            ->call('create')
            ->assertHasFormErrors(['path']);

        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_assets_in_use_cannot_be_deleted_and_unused_ones_take_their_file_with_them(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $inUse = MediaAsset::factory()->create();
        HomepageHeroSlide::factory()->for($inUse, 'mediaAsset')->create();

        $this->assertFalse(Gate::forUser($admin)->allows('delete', $inUse));
        Livewire::test(EditMediaAsset::class, ['record' => $inUse->id])
            ->assertActionHidden(DeleteAction::class);

        Storage::disk('public')->put('media/unused.jpg', UploadedFile::fake()->image('unused.jpg', 800, 600)->getContent());
        $unused = MediaAsset::factory()->create(['path' => 'media/unused.jpg']);

        Livewire::test(EditMediaAsset::class, ['record' => $unused->id])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($unused);
        Storage::disk('public')->assertMissing('media/unused.jpg');
    }

    public function test_replacing_a_file_removes_the_previous_one(): void
    {
        Storage::disk('public')->put('media/old.jpg', UploadedFile::fake()->image('old.jpg', 800, 600)->getContent());
        Storage::disk('public')->put('media/new.jpg', UploadedFile::fake()->image('new.jpg', 1200, 900)->getContent());

        $asset = MediaAsset::factory()->create(['path' => 'media/old.jpg']);
        $this->assertSame(800, $asset->width);

        $asset->update(['path' => 'media/new.jpg']);

        $this->assertSame(1200, $asset->refresh()->width);
        Storage::disk('public')->assertMissing('media/old.jpg');
        Storage::disk('public')->assertExists('media/new.jpg');
    }
}
