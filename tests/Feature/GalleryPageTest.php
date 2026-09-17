<?php

use App\Models\GalleryItem;
use Illuminate\Support\Facades\Storage;

beforeEach(function () { Storage::fake('public'); });

test('gallery page loads empty state and links from homepage', function () {
    $this->get('/gallery')->assertOk()->assertSee('ستُعرض صور الحياة المدرسية هنا بعد اعتمادها ونشرها.');
    $this->get('/')->assertOk()->assertSee('href="'.route('gallery.index').'"', false);
});

test('gallery page shows only published existing images ordered by featured then sort order', function () {
    Storage::disk('public')->put('gallery/one.jpg', 'image');
    Storage::disk('public')->put('gallery/two.jpg', 'image');
    GalleryItem::query()->create(['image' => 'gallery/two.jpg', 'alt_text' => 'ثانية', 'sort_order' => 0, 'is_active' => true]);
    GalleryItem::query()->create(['image' => 'gallery/one.jpg', 'alt_text' => 'أولى', 'sort_order' => 9, 'is_featured' => true, 'is_active' => true]);
    GalleryItem::query()->create(['image' => 'gallery/missing.jpg', 'alt_text' => 'مفقودة', 'is_active' => true]);
    GalleryItem::query()->create(['image' => 'gallery/hidden.jpg', 'alt_text' => 'مخفية', 'is_active' => false]);
    $this->get('/gallery')->assertOk()->assertSeeInOrder(['أولى', 'ثانية'])->assertDontSee('مخفية')->assertDontSee('مفقودة');
});

test('gallery page escapes text and homepage uses preview instead of image grid', function () {
    Storage::disk('public')->put('gallery/one.jpg', 'image');
    GalleryItem::query()->create(['image' => 'gallery/one.jpg', 'alt_text' => '<script>alert(1)</script>', 'is_active' => true]);
    $this->get('/gallery')->assertOk()->assertSee('<script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>', false);
    $this->get('/')->assertOk()->assertSee('عرض معرض الصور الكامل')->assertDontSee('data-gallery-slideshow', false);
});

test('gallery page navigation links return to the homepage sections', function () {
    $this->get('/gallery')->assertOk()->assertSee('href="'.route('home').'#stages"', false)->assertSee('href="'.route('home').'#contact"', false);
});
