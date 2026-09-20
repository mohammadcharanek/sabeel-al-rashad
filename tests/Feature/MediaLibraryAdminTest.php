<?php

use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Filament\Resources\MediaFolders\Pages\CreateMediaFolder;
use App\Filament\Resources\MediaFolders\Pages\EditMediaFolder;
use App\Filament\Resources\MediaFolders\Pages\ListMediaFolders;
use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Filament\Resources\Videos\Pages\EditVideo;
use App\Filament\Resources\Videos\Pages\ListVideos;
use App\Models\GalleryItem;
use App\Models\MediaFolder;
use App\Models\User;
use App\Models\Video;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'production']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('guests cannot access any media management resource', function (string $path) {
    $this->get('/admin/'.$path)->assertRedirect('/admin/login');
})->with(['gallery-items', 'gallery-items/create', 'media-folders', 'media-folders/create', 'videos', 'videos/create']);

test('non administrators cannot access media management or call its Livewire pages', function (string $path, string $page) {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/'.$path)->assertForbidden();
    Livewire::test($page)->assertForbidden();
})->with([
    ['gallery-items/create', CreateGalleryItem::class],
    ['media-folders/create', CreateMediaFolder::class],
    ['videos/create', CreateVideo::class],
    ['media-folders', ListMediaFolders::class],
    ['videos', ListVideos::class],
]);

test('media policies restrict read write delete and reorder permissions to administrators', function (string $model) {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();
    $record = $model::factory()->create();

    foreach (['viewAny', 'create', 'deleteAny', 'reorder'] as $ability) {
        expect(Gate::forUser($admin)->allows($ability, $model))->toBeTrue();
        expect(Gate::forUser($other)->allows($ability, $model))->toBeFalse();
    }
    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::forUser($admin)->allows($ability, $record))->toBeTrue();
        expect(Gate::forUser($other)->allows($ability, $record))->toBeFalse();
    }
})->with([GalleryItem::class, MediaFolder::class, Video::class]);

test('administrator creates an unpublished folder with cover then approves and hides it', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateMediaFolder::class)->fillForm([
        'title' => 'مجلد الأنشطة', 'slug' => 'school-activities', 'media_type' => 'photo',
        'cover' => UploadedFile::fake()->image('cover.jpg'),
    ])->call('create')->assertHasNoFormErrors();

    $folder = MediaFolder::query()->sole();
    expect($folder->is_published)->toBeFalse();
    Storage::disk('public')->assertExists($folder->cover);
    $this->get('/gallery')->assertDontSee($folder->title);
    Livewire::test(EditMediaFolder::class, ['record' => $folder->id])
        ->fillForm(['is_published' => true])->call('save')->assertHasNoFormErrors();
    $this->get('/gallery')->assertSee($folder->title);
    Livewire::test(EditMediaFolder::class, ['record' => $folder->id])
        ->fillForm(['is_published' => false])->call('save')->assertHasNoFormErrors();
    $this->get('/gallery')->assertDontSee($folder->title);
});

test('folder form rejects duplicate slug invalid type and unsafe slug', function () {
    $this->actingAs(User::factory()->admin()->create());
    $folder = MediaFolder::factory()->create(['slug' => 'existing']);

    Livewire::test(CreateMediaFolder::class)->fillForm(['title' => 'مجلد', 'slug' => 'existing', 'media_type' => 'photo'])
        ->call('create')->assertHasFormErrors(['slug' => 'unique']);
    Livewire::test(CreateMediaFolder::class)->fillForm(['title' => 'مجلد', 'slug' => '../unsafe', 'media_type' => 'executable'])
        ->call('create')->assertHasFormErrors(['slug' => 'regex', 'media_type']);
    $this->assertDatabaseCount('media_folders', 1);
});

test('administrator reorders folders through the resource and public gallery follows the saved order', function () {
    $this->actingAs(User::factory()->admin()->create());
    $first = MediaFolder::factory()->published()->create(['sort_order' => 1]);
    $second = MediaFolder::factory()->published()->create(['sort_order' => 2]);

    Livewire::test(ListMediaFolders::class)->call('reorderTable', [$second->id, $first->id]);

    expect($second->fresh()->sort_order)->toBeLessThan($first->fresh()->sort_order);
    $this->get('/gallery')->assertViewHas('folders', fn ($folders) => $folders->modelKeys() === [$second->id, $first->id]);
});

test('administrator uploads a video and poster unpublished then approves publication', function (string $extension, string $mime) {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $folder = MediaFolder::factory()->video()->published()->create();

    Livewire::test(CreateVideo::class)->fillForm([
        'title' => 'فيديو المدرسة', 'description' => 'وصف المحتوى', 'media_folder_id' => $folder->id,
        'video' => UploadedFile::fake()->create('school.'.$extension, 100, $mime),
        'poster' => UploadedFile::fake()->image('poster.png'),
    ])->call('create')->assertHasNoFormErrors();

    $video = Video::query()->sole();
    expect($video->is_published)->toBeFalse();
    Storage::disk('public')->assertExists([$video->video, $video->poster]);
    $this->get('/videos/'.$folder->slug)->assertDontSee($video->video, false);
    Livewire::test(EditVideo::class, ['record' => $video->id])->fillForm(['is_published' => true])
        ->call('save')->assertHasNoFormErrors();
    $this->get('/videos/'.$folder->slug)->assertSee($video->video, false);
})->with([['mp4', 'video/mp4'], ['webm', 'video/webm']]);

test('moving photos between folders changes only the assignment and permits clearing it', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $first = MediaFolder::factory()->create();
    $second = MediaFolder::factory()->create();
    $photo = GalleryItem::factory()->for($first, 'folder')->create(['image' => 'gallery/original.jpg']);
    Storage::disk('public')->put($photo->image, 'original bytes');

    Livewire::test(EditGalleryItem::class, ['record' => $photo->id])->fillForm(['media_folder_id' => $second->id])
        ->call('save')->assertHasNoFormErrors();
    expect($photo->fresh()->media_folder_id)->toBe($second->id);
    expect($photo->fresh()->image)->toBe('gallery/original.jpg');
    expect(Storage::disk('public')->get('gallery/original.jpg'))->toBe('original bytes');
    Livewire::test(EditGalleryItem::class, ['record' => $photo->id])->fillForm(['media_folder_id' => null])
        ->call('save')->assertHasNoFormErrors();
    expect($photo->fresh()->media_folder_id)->toBeNull();
    Storage::disk('public')->assertExists('gallery/original.jpg');
});

test('moving videos preserves the file and poster while mismatched photo folders are rejected', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $folder = MediaFolder::factory()->video()->create();
    $photoFolder = MediaFolder::factory()->create();
    $video = Video::factory()->create(['video' => 'videos/original.mp4', 'poster' => 'video-posters/original.jpg']);
    Storage::disk('public')->put($video->video, 'video bytes');
    Storage::disk('public')->put($video->poster, 'poster bytes');

    Livewire::test(EditVideo::class, ['record' => $video->id])->fillForm(['media_folder_id' => $folder->id])
        ->call('save')->assertHasNoFormErrors();
    expect($video->fresh()->media_folder_id)->toBe($folder->id);
    expect($video->fresh()->video)->toBe('videos/original.mp4');
    expect(Storage::disk('public')->get('videos/original.mp4'))->toBe('video bytes');
    expect(Storage::disk('public')->get('video-posters/original.jpg'))->toBe('poster bytes');
    Livewire::test(EditVideo::class, ['record' => $video->id])->fillForm(['media_folder_id' => $photoFolder->id])
        ->call('save')->assertHasFormErrors(['media_folder_id']);
    expect($video->fresh()->media_folder_id)->toBe($folder->id);
});

test('photo assignments reject video folders and nonexistent folders', function (bool $existing) {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $folderId = $existing ? MediaFolder::factory()->video()->create()->id : 999;

    Livewire::test(CreateGalleryItem::class)->fillForm([
        'image' => UploadedFile::fake()->image('photo.jpg'), 'alt_text' => 'صورة المدرسة', 'media_folder_id' => $folderId,
    ])->call('create')->assertHasFormErrors(['media_folder_id']);
    $this->assertDatabaseCount('gallery_items', 0);
})->with([true, false]);

test('folder deletion is blocked until reassignment and never deletes stored media or cover', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $folder = MediaFolder::factory()->create(['cover' => 'media-covers/retained.jpg']);
    $photo = GalleryItem::factory()->for($folder, 'folder')->create();
    Storage::disk('public')->put($photo->image, 'photo');
    Storage::disk('public')->put($folder->cover, 'cover');
    expect(Gate::forUser($admin)->allows('delete', $folder))->toBeFalse();
    Livewire::test(EditMediaFolder::class, ['record' => $folder->id])->assertActionHidden(DeleteAction::class);

    $photo->update(['media_folder_id' => null]);
    Livewire::test(EditMediaFolder::class, ['record' => $folder->id])->callAction(DeleteAction::class);

    $this->assertModelMissing($folder);
    $this->assertModelExists($photo);
    Storage::disk('public')->assertExists([$photo->image, 'media-covers/retained.jpg']);
});

test('uploads reject forged paths for videos posters covers and photos', function (string $page, string $field, string $path) {
    Storage::fake('public');
    Storage::disk('public')->put('videos/another.mp4', 'existing');
    Storage::disk('public')->put('video-posters/another.jpg', 'existing');
    Storage::disk('public')->put('media-covers/another.jpg', 'existing');
    Storage::disk('public')->put('gallery/another.jpg', 'existing');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test($page)->set('data.'.$field, ['forged' => $path])->call('create')->assertHasFormErrors([$field]);

    $this->assertDatabaseCount('videos', 0);
    $this->assertDatabaseCount('media_folders', 0);
    $this->assertDatabaseCount('gallery_items', 0);
})->with([
    [CreateVideo::class, 'video', 'videos/another.mp4'],
    [CreateVideo::class, 'video', '../private.mp4'],
    [CreateVideo::class, 'poster', 'video-posters/another.jpg'],
    [CreateMediaFolder::class, 'cover', 'media-covers/another.jpg'],
    [CreateGalleryItem::class, 'image', 'gallery/another.jpg'],
]);

test('video uploads reject executables spoofed contents unsupported formats and excessive size', function (string $kind) {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $upload = match ($kind) {
        'executable' => UploadedFile::fake()->create('danger.php', 1, 'application/x-httpd-php'),
        'spoofed' => UploadedFile::fake()->createWithContent('pretend.mp4', '<?php echo "unsafe";'),
        'extension' => UploadedFile::fake()->create('danger.php', 1, 'video/mp4'),
        'unsupported' => UploadedFile::fake()->create('movie.avi', 1, 'video/x-msvideo'),
        'oversized' => UploadedFile::fake()->create('large.mp4', 102401, 'video/mp4'),
    };

    Livewire::test(CreateVideo::class)->fillForm(['title' => 'فيديو', 'video' => $upload])
        ->call('create')->assertHasFormErrors(['video']);

    $this->assertDatabaseCount('videos', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with(['executable', 'spoofed', 'extension', 'unsupported', 'oversized']);

test('photo cover and poster uploads reject active images and excessive size', function (string $page, string $field, string $kind) {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $upload = $kind === 'svg'
        ? UploadedFile::fake()->createWithContent('active.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')
        : UploadedFile::fake()->image('large.jpg')->size(5121);

    Livewire::test($page)->fillForm([$field => $upload])->call('create')->assertHasFormErrors([$field]);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    [CreateGalleryItem::class, 'image', 'svg'], [CreateGalleryItem::class, 'image', 'size'],
    [CreateMediaFolder::class, 'cover', 'svg'], [CreateMediaFolder::class, 'cover', 'size'],
    [CreateVideo::class, 'poster', 'svg'], [CreateVideo::class, 'poster', 'size'],
]);
