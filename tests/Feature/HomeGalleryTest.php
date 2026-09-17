<?php

use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Models\GalleryItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

test('homepage shows only active gallery images in featured and configured order', function () {
    Storage::disk('public')->put('gallery/first.jpg', 'image');
    Storage::disk('public')->put('gallery/second.jpg', 'image');
    $second = GalleryItem::query()->create(['image' => 'gallery/second.jpg', 'alt_text' => 'ثانية', 'sort_order' => 1, 'is_active' => true]);
    $first = GalleryItem::query()->create(['image' => 'gallery/first.jpg', 'alt_text' => 'أولى', 'sort_order' => 9, 'is_featured' => true, 'is_active' => true]);
    GalleryItem::query()->create(['image' => 'gallery/hidden.jpg', 'alt_text' => 'مخفية', 'is_active' => false]);
    GalleryItem::query()->create(['image' => 'gallery/missing.jpg', 'alt_text' => 'مفقودة', 'is_active' => true]);

    $this->get('/')->assertOk()
        ->assertViewHas('galleryItems', fn ($items) => $items->modelKeys() === [$first->id, $second->id])
        ->assertSeeInOrder(['أولى', 'ثانية'])
        ->assertDontSee('مخفية')->assertDontSee('مفقودة');
});

test('gallery text is escaped and unsafe paths are not rendered', function () {
    Storage::disk('public')->put('gallery/safe.jpg', 'image');
    GalleryItem::query()->create(['image' => 'gallery/safe.jpg', 'alt_text' => '<script>alert(1)</script>', 'caption' => '<img src=x onerror=alert(1)>', 'is_active' => true]);
    GalleryItem::query()->create(['image' => '../settings/private.jpg', 'alt_text' => 'private', 'is_active' => true]);
    $this->get('/')->assertOk()
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertDontSee('private');
});

test('gallery without published valid images displays a neutral message', function () {
    $this->get('/')->assertOk()->assertSee('ستُعرض صور الحياة المدرسية هنا بعد اعتمادها ونشرها.');
});

test('non administrator cannot open gallery CMS', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->create());
    $this->get('/admin/gallery-items')->assertForbidden();
});

test('administrator uploads image and creates an unpublished gallery item by default', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateGalleryItem::class)
        ->fillForm(['image' => UploadedFile::fake()->image('school.jpg'), 'alt_text' => 'صورة من المدرسة', 'sort_order' => 0])
        ->call('create')->assertHasNoFormErrors();

    $record = GalleryItem::query()->firstOrFail();
    expect($record->is_active)->toBeFalse();
    Storage::disk('public')->assertExists($record->image);
    $this->get('/')->assertDontSee('صورة من المدرسة');
});
