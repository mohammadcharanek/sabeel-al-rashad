<?php
use Illuminate\Filesystem\FilesystemAdapter;
use App\Models\NewsPost;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

test('homepage only shows news that is published and due', function (bool $published, ?string $date, bool $visible) {
    /** @var TestCase $this */

    $this->travelTo('2026-09-14 12:00:00');

    $post = NewsPost::factory()->create([
        'is_published' => $published,
        'published_at' => $date,
    ]);

    $response = $this->get('/');

    $response->assertViewHas(
        'newsPosts',
        fn ($posts) => $visible
            ? $posts->modelKeys() === [$post->id]
            : $posts->isEmpty()
    );
})->with([
    'draft without date' => [false, null, false],
    'draft with past date' => [false, '2026-09-13 12:00:00', false],
    'scheduled' => [true, '2026-09-14 12:00:01', false],
    'due now' => [true, '2026-09-14 12:00:00', true],
    'past publication' => [true, '2026-09-13 12:00:00', true],
    'immediate publication' => [true, null, true],
]);

test('homepage shows three news cards in editorial and publication order with stable ties', function () {
    /** @var TestCase $this */

    $this->travelTo('2026-09-14 12:00:00');

    $omitted = NewsPost::factory()->published()->create([
        'sort_order' => 2,
    ]);

    $older = NewsPost::factory()->published()->create([
        'sort_order' => 1,
        'published_at' => '2026-09-12 12:00:00',
    ]);

    $newer = NewsPost::factory()->published()->create([
        'sort_order' => 1,
        'published_at' => '2026-09-13 12:00:00',
    ]);

    $tie = NewsPost::factory()->published()->create([
        'sort_order' => 1,
        'published_at' => '2026-09-13 12:00:00',
    ]);

    $priority = NewsPost::factory()->published()->create([
        'sort_order' => 0,
        'published_at' => null,
    ]);

    $this->get('/')
        ->assertViewHas(
            'newsPosts',
            fn ($posts) => $posts->modelKeys() === [
                $priority->id,
                $tie->id,
                $newer->id,
            ]
        )
        ->assertSeeInOrder([
            $priority->title,
            $tie->title,
            $newer->title,
        ])
        ->assertDontSee($older->title)
        ->assertDontSee($omitted->title);
});

test('homepage renders stored news fields and public disk image URLs safely', function () {
    /** @var TestCase $this */

    Storage::fake('public', [
        'url' => 'https://images.example.test/news-media',
    ]);

    Storage::disk('public')->put(
        'news/example.jpg',
        'test image'
    );

    $this->travelTo('2026-09-14 12:00:00');

    $post = NewsPost::factory()->published()->create([
        'title' => '<script>alert("title")</script>',
        'excerpt' => '<img src=x onerror=alert(1)>',
        'category' => 'أخبار المدرسة',
        'featured_image' => 'news/example.jpg',
        'published_at' => '2026-09-13 12:00:00',
    ]);

    $this->get('/')
        ->assertSee($post->title)
        ->assertSee($post->excerpt)
        ->assertSee('أخبار المدرسة')
        ->assertSee('13 سبتمبر 2026')
        ->assertSee(
            'https://images.example.test/news-media/news/example.jpg',
            false
        )
        ->assertDontSee($post->title, false)
        ->assertDontSee($post->excerpt, false);

    /** @var FilesystemAdapter $disk */
$disk = Storage::disk('public');

$disk->assertExists('news/example.jpg');
});

test('homepage keeps the card placeholder when no usable image exists', function (?string $image) {
    /** @var TestCase $this */

    Storage::fake('public');

    $post = NewsPost::factory()->published()->create([
        'featured_image' => $image,
    ]);

    $this->get('/')
        ->assertSee($post->title)
        ->assertSee('صورة الخبر');

    expect($post->featured_image_url)->toBeNull();

    /** @var FilesystemAdapter $disk */
$disk = Storage::disk('public');

$disk->assertMissing('news/missing.jpg');
})->with([
    null,
    'news/missing.jpg',
]);

test('homepage shows its existing empty state when nothing is public', function () {
    /** @var TestCase $this */

    NewsPost::factory()->create();

    $this->get('/')
        ->assertSee('لا توجد أخبار منشورة حالياً.');
});