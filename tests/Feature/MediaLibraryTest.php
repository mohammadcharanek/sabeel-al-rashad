<?php

use App\Models\GalleryItem;
use App\Models\MediaFolder;
use App\Models\SiteSetting;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('photo folders are published by type and ordered without hiding legacy unassigned photos', function () {
    Storage::fake('public');
    $second = MediaFolder::factory()->published()->create(['sort_order' => 20]);
    $first = MediaFolder::factory()->published()->create(['sort_order' => 1]);
    $hidden = MediaFolder::factory()->create();
    $videoFolder = MediaFolder::factory()->video()->published()->create();
    $photo = GalleryItem::factory()->published()->create(['image' => 'gallery/legacy.jpg']);
    Storage::disk('public')->put($photo->image, 'existing photo');

    $this->get(route('gallery.index'))
        ->assertViewHas('folders', fn ($folders) => $folders->modelKeys() === [$first->id, $second->id])
        ->assertViewHas('galleryItems', fn ($items) => $items->modelKeys() === [$photo->id])
        ->assertSee('صور غير مصنفة')->assertSee($photo->alt_text)
        ->assertSee(route('gallery.show', ['folder' => $first->slug]), false)
        ->assertDontSee($hidden->title)->assertDontSee($videoFolder->title);
});

test('photo folder contains only its published photographs with slideshow captions and controls', function () {
    Storage::fake('public');
    $folder = MediaFolder::factory()->published()->create();
    $otherFolder = MediaFolder::factory()->published()->create();
    $second = GalleryItem::factory()->published()->for($folder, 'folder')->create(['sort_order' => 1]);
    $first = GalleryItem::factory()->published()->for($folder, 'folder')->create(['is_featured' => true, 'sort_order' => 9, 'caption' => 'تعليق الصورة']);
    $draft = GalleryItem::factory()->for($folder, 'folder')->create();
    $other = GalleryItem::factory()->published()->for($otherFolder, 'folder')->create();
    $unassigned = GalleryItem::factory()->published()->create();
    foreach ([$first, $second, $draft, $other, $unassigned] as $photo) {
        Storage::disk('public')->put($photo->image, 'image');
    }

    $this->get(route('gallery.show', ['folder' => $folder->slug]))
        ->assertViewHas('galleryItems', fn ($items) => $items->modelKeys() === [$first->id, $second->id])
        ->assertSee('تعليق الصورة')->assertSee('data-gallery-prev', false)->assertSee('data-gallery-next', false)
        ->assertSee('data-gallery-go-to', false)->assertSee('js/gallery.js', false)
        ->assertDontSee($draft->image)->assertDontSee($other->image)->assertDontSee($unassigned->image);
});

test('hiding a photo folder removes its photos from the homepage and denies its public route', function () {
    Storage::fake('public');
    $folder = MediaFolder::factory()->published()->create();
    $photo = GalleryItem::factory()->published()->for($folder, 'folder')->create();
    Storage::disk('public')->put($photo->image, 'image');
    $this->get(route('home'))->assertSee($photo->image, false);

    $folder->update(['is_published' => false]);

    $this->get(route('home'))->assertDontSee($photo->image, false);
    $this->get(route('gallery.index'))->assertDontSee($photo->image, false)->assertDontSee($folder->title);
    $this->get(route('gallery.show', ['folder' => $folder->slug]))->assertNotFound();
    Storage::disk('public')->assertExists($photo->image);
});

test('folder routes reject a wrong media type or nonexistent slug', function () {
    $photo = MediaFolder::factory()->published()->create();
    $video = MediaFolder::factory()->video()->published()->create();
    $draftVideo = MediaFolder::factory()->video()->create();

    $this->get(route('gallery.show', ['folder' => $video->slug]))->assertNotFound();
    $this->get(route('videos.show', ['folder' => $photo->slug]))->assertNotFound();
    $this->get(route('videos.show', ['folder' => $draftVideo->slug]))->assertNotFound();
    $this->get('/gallery/missing-folder')->assertNotFound();
    $this->get('/videos/missing-folder')->assertNotFound();
});

test('video folders are published by type and sorted with unassigned published videos visible', function () {
    Storage::fake('public');
    $second = MediaFolder::factory()->video()->published()->create(['sort_order' => 5]);
    $first = MediaFolder::factory()->video()->published()->create(['sort_order' => 1]);
    $hidden = MediaFolder::factory()->video()->create();
    $photo = MediaFolder::factory()->published()->create();
    $video = Video::factory()->published()->create();
    Storage::disk('public')->put($video->video, 'video');

    $this->get(route('videos.index'))
        ->assertViewHas('folders', fn ($folders) => $folders->modelKeys() === [$first->id, $second->id])
        ->assertViewHas('videos', fn ($videos) => $videos->modelKeys() === [$video->id])
        ->assertSee('فيديوهات غير مصنفة')->assertSee($video->title)
        ->assertDontSee($hidden->title)->assertDontSee($photo->title);
});

test('video folder renders accessible native players in order without drafts or unrelated files', function () {
    Storage::fake('public');
    $folder = MediaFolder::factory()->video()->published()->create();
    $second = Video::factory()->published()->for($folder, 'folder')->create(['sort_order' => 8]);
    $first = Video::factory()->published()->for($folder, 'folder')->create(['sort_order' => 1, 'poster' => 'video-posters/first.jpg', 'description' => 'ملخص الفيديو']);
    $draft = Video::factory()->for($folder, 'folder')->create(['poster' => 'video-posters/draft.jpg']);
    $outside = Video::factory()->published()->create();
    $missing = Video::factory()->published()->for($folder, 'folder')->create();
    foreach ([$first, $second, $draft, $outside] as $video) {
        Storage::disk('public')->put($video->video, 'video');
    }
    Storage::disk('public')->put($first->poster, 'image');
    Storage::disk('public')->put($draft->poster, 'image');

    $this->get(route('videos.show', ['folder' => $folder->slug]))
        ->assertViewHas('videos', fn ($videos) => $videos->modelKeys() === [$first->id, $second->id])
        ->assertSee('<video controls playsinline preload="none"', false)
        ->assertSee('poster="'.$first->posterUrl().'"', false)
        ->assertSee('aria-labelledby="video-title-'.$first->id.'"', false)
        ->assertSee('aria-describedby="video-description-'.$first->id.'"', false)
        ->assertSee('type="video/mp4"', false)->assertSee('ملخص الفيديو')
        ->assertDontSee('autoplay', false)->assertDontSee($draft->title)
        ->assertDontSee($draft->video)->assertDontSee($draft->poster)
        ->assertDontSee($outside->video)->assertDontSee($missing->video);
});

test('unpublished video files never appear in public listings including hidden folders', function () {
    Storage::fake('public');
    $folder = MediaFolder::factory()->video()->create();
    $hidden = Video::factory()->published()->for($folder, 'folder')->create();
    $draft = Video::factory()->create();
    foreach ([$hidden, $draft] as $video) {
        Storage::disk('public')->put($video->video, 'video');
    }

    foreach (['home', 'gallery.index', 'videos.index'] as $route) {
        $this->get(route($route))->assertDontSee($hidden->video)->assertDontSee($draft->video);
    }
});

test('unsafe video and image paths never produce public media URLs', function (string $path) {
    Storage::fake('public');
    $video = Video::factory()->published()->create(['video' => $path, 'poster' => $path]);
    $folder = MediaFolder::factory()->published()->create(['cover' => $path]);

    expect($video->videoUrl())->toBeNull();
    expect($video->posterUrl())->toBeNull();
    expect($folder->coverUrl())->toBeNull();
    $this->get(route('videos.index'))->assertDontSee('<source', false);
})->with(['../private.mp4', 'videos/../../private.mp4', 'https://example.com/file.mp4', '/videos/movie.mp4', 'videos//movie.mp4', 'videos/movie.php', 'videos/movie.php.mp4', 'videos/%2e%2e/movie.mp4', 'videos\\movie.mp4']);

test('webm video and escaped text render without requiring a poster', function () {
    Storage::fake('public');
    $video = Video::factory()->published()->create(['video' => 'videos/school.webm', 'title' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(1)>']);
    Storage::disk('public')->put($video->video, 'video');

    $this->get(route('videos.index'))->assertSee('type="video/webm"', false)
        ->assertSee($video->title)->assertDontSee($video->title, false)
        ->assertSee($video->description)->assertDontSee($video->description, false)
        ->assertDontSee('poster=', false);
});

test('folder descriptions are escaped and valid covers have accessible labels', function () {
    Storage::fake('public');
    $folder = MediaFolder::factory()->published()->create(['title' => '<script>bad()</script>', 'description' => '<img src=x onerror=bad()>', 'cover' => 'media-covers/cover.jpg']);
    Storage::disk('public')->put($folder->cover, 'image');

    $this->get(route('gallery.index'))->assertSee($folder->title)->assertDontSee($folder->title, false)
        ->assertSee($folder->description)->assertDontSee($folder->description, false)
        ->assertSee('alt="'.e('غلاف مجلد '.$folder->title).'"', false)->assertSee($folder->cover, false);
});

test('empty media libraries and empty published folders show meaningful messages', function () {
    $this->get(route('videos.index'))->assertSee('ستُعرض فيديوهات الحياة المدرسية هنا بعد اعتمادها ونشرها.');
    $photo = MediaFolder::factory()->published()->create();
    $video = MediaFolder::factory()->video()->published()->create();

    $this->get(route('gallery.show', ['folder' => $photo->slug]))->assertSee('لا توجد صور معتمدة ومنشورة في هذا المجلد بعد.');
    $this->get(route('videos.show', ['folder' => $video->slug]))->assertSee('لا توجد فيديوهات معتمدة ومنشورة في هذا المجلد بعد.');
});

test('desktop mobile and footer navigation link both galleries and preserve dynamic school settings', function (string $route) {
    SiteSetting::factory()->create(['school_name_ar' => 'اسم المدرسة من الإعدادات']);

    $response = $this->get(route($route))->assertSee('اسم المدرسة من الإعدادات')
        ->assertSee('معرض الصور')->assertSee('معرض الفيديو')
        ->assertDontSee('<video', false);
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);
    foreach (['التنقل الرئيسي', 'التنقل الرئيسي للجوال', 'روابط سريعة'] as $label) {
        foreach (['gallery.index', 'videos.index'] as $target) {
            expect($xpath->query('//nav[@aria-label="'.$label.'"]//a[@href="'.route($target).'"]')->length)->toBe(1);
        }
    }
})->with(['home', 'gallery.index', 'videos.index']);

test('nonempty folders cannot be deleted or change media type and files remain intact', function (string $type) {
    Storage::fake('public');
    $folder = MediaFolder::factory()->create(['media_type' => $type]);
    $item = $type === 'photo'
        ? GalleryItem::factory()->for($folder, 'folder')->create()
        : Video::factory()->for($folder, 'folder')->create();
    $path = $type === 'photo' ? $item->image : $item->video;
    Storage::disk('public')->put($path, 'original file');

    expect(fn () => $folder->delete())->toThrow(ValidationException::class);
    expect(fn () => $folder->update(['media_type' => $type === 'photo' ? 'video' : 'photo']))->toThrow(ValidationException::class);
    $this->assertModelExists($folder);
    $this->assertModelExists($item);
    Storage::disk('public')->assertExists($path);
})->with(['photo', 'video']);
