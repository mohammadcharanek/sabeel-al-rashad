<?php

use App\Filament\Resources\NewsPosts\NewsPostResource;
use App\Filament\Resources\NewsPosts\Pages\CreateNewsPost;
use App\Filament\Resources\NewsPosts\Pages\EditNewsPost;
use App\Filament\Resources\NewsPosts\Pages\ListNewsPosts;
use App\Filament\Resources\NewsPosts\Pages\ViewNewsPost;
use App\Models\NewsPost;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'production']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('news CMS requires authentication', function () {
    $this->get(NewsPostResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
});

test('CMS creates a published news record with an uploaded image visible on the homepage', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $this->travelTo('2026-09-14 12:00:00');

    Livewire::test(CreateNewsPost::class)
        ->fillForm([
            'title' => 'خبر من إدارة المدرسة',
            'slug' => 'school-update',
            'excerpt' => 'ملخص الخبر المنشور',
            'category' => 'أخبار المدرسة',
            'body' => '<p>محتوى الخبر</p>',
            'featured_image' => UploadedFile::fake()->image('news.jpg'),
            'is_published' => true,
            'published_at' => '2026-09-14 11:00:00',
            'sort_order' => 3,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('news_posts', ['slug' => 'school-update', 'is_published' => true, 'sort_order' => 3]);
    $post = NewsPost::query()->where('slug', 'school-update')->firstOrFail();
    Storage::disk('public')->assertExists($post->featured_image);
    $this->get('/')->assertSee('خبر من إدارة المدرسة')->assertSee('ملخص الخبر المنشور')->assertSee($post->featured_image_url, false);
});

test('CMS keeps a custom slug when an existing title is edited and can unpublish the record', function () {
    $this->actingAs(User::factory()->admin()->create());
    $post = NewsPost::factory()->published()->create(['slug' => 'keep-this-slug']);

    Livewire::test(EditNewsPost::class, ['record' => $post->id])
        ->fillForm(['title' => 'Updated headline', 'is_published' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('news_posts', ['id' => $post->id, 'title' => 'Updated headline', 'slug' => 'keep-this-slug', 'is_published' => false]);
    $this->get('/')->assertDontSee('Updated headline');
});

test('new titles generate a slug until the editor customizes it', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateNewsPost::class)
        ->fillForm(['title' => 'First headline'])
        ->assertSchemaStateSet(['slug' => 'first-headline'])
        ->fillForm(['title' => 'Second headline'])
        ->assertSchemaStateSet(['slug' => 'second-headline'])
        ->fillForm(['slug' => 'custom-link', 'title' => 'Final headline'])
        ->assertSchemaStateSet(['slug' => 'custom-link']);
});

test('CMS rejects invalid ordering before saving', function (mixed $order, string $rule) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateNewsPost::class)
        ->fillForm(['title' => 'Validation news', 'slug' => 'validation-news', 'sort_order' => $order])
        ->call('create')
        ->assertHasFormErrors(['sort_order' => $rule]);

    $this->assertDatabaseCount('news_posts', 0);
})->with(['empty' => [null, 'required'], 'fraction' => [1.5, 'integer'], 'negative' => [-1, 'min'], 'overflow' => [4294967296, 'max']]);

test('CMS refuses duplicate slugs', function () {
    $this->actingAs(User::factory()->admin()->create());
    NewsPost::factory()->create(['slug' => 'existing-news']);

    Livewire::test(CreateNewsPost::class)
        ->fillForm(['title' => 'Another title', 'slug' => 'existing-news'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);

    $this->assertDatabaseCount('news_posts', 1);
});

test('CMS lists drafts and published records and renders the public image and rich body', function () {
    Storage::fake('public');
    Storage::disk('public')->put('news/view.jpg', 'test image');
    $this->actingAs(User::factory()->admin()->create());
    $draft = NewsPost::factory()->create();
    $post = NewsPost::factory()->published()->create(['featured_image' => 'news/view.jpg', 'body' => '<p><strong>Formatted body</strong></p><script>alert(1)</script>']);

    Livewire::test(ListNewsPosts::class)->assertCanSeeTableRecords([$draft, $post]);
    Livewire::test(ViewNewsPost::class, ['record' => $post->id])
        ->assertSee(Storage::disk('public')->url('news/view.jpg'), false)
        ->assertSee('<strong>Formatted body</strong>', false)
        ->assertDontSee('<script>alert(1)</script>', false);

    Storage::disk('public')->assertExists('news/view.jpg');
});

test('CMS can delete a news record', function () {
    $this->actingAs(User::factory()->admin()->create());
    $post = NewsPost::factory()->create();

    Livewire::test(EditNewsPost::class, ['record' => $post->id])->callAction(DeleteAction::class);

    $this->assertModelMissing($post);
});
