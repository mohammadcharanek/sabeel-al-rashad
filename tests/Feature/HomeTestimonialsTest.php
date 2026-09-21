<?php

use App\Models\HomepageSetting;
use App\Models\Testimonial;

test('homepage shows at most three approved published testimonials ordered by sort order then id', function () {
    $fourth = Testimonial::factory()->published()->create(['quote' => 'Fourth testimonial', 'sort_order' => 9]);
    $second = Testimonial::factory()->published()->create(['quote' => 'Second testimonial', 'sort_order' => 2]);
    $third = Testimonial::factory()->published()->create(['quote' => 'Third testimonial', 'sort_order' => 2]);
    $first = Testimonial::factory()->published()->create(['quote' => 'First testimonial', 'sort_order' => 1]);
    Testimonial::factory()->create(['quote' => 'Unapproved draft', 'sort_order' => 0]);
    Testimonial::factory()->approved()->create(['quote' => 'Approved draft', 'sort_order' => 0]);
    Testimonial::factory()->create(['quote' => 'Legacy published without consent', 'is_published' => true, 'sort_order' => 0]);

    $this->get('/')->assertOk()
        ->assertViewHas('testimonials', fn ($items) => $items->modelKeys() === [$first->id, $second->id, $third->id])
        ->assertSeeInOrder(['First testimonial', 'Second testimonial', 'Third testimonial'])
        ->assertDontSee($fourth->quote)->assertDontSee('Unapproved draft')
        ->assertDontSee('Approved draft')->assertDontSee('Legacy published without consent');
});

test('homepage escapes testimonial text public name and attribution', function () {
    Testimonial::factory()->published()->create([
        'quote' => '<script>alert("quote")</script>',
        'person_name' => '<img src=x onerror=alert("name")>',
        'person_role' => '<svg onload=alert("role")>',
    ]);

    $this->get('/')->assertOk()
        ->assertSee('<script>alert("quote")</script>')->assertDontSee('<script>alert("quote")</script>', false)
        ->assertSee('<img src=x onerror=alert("name")>')->assertDontSee('<img src=x onerror=alert("name")>', false)
        ->assertSee('<svg onload=alert("role")>')->assertDontSee('<svg onload=alert("role")>', false);
});

test('testimonial cards render optional attribution without invented ratings or avatars', function () {
    Testimonial::factory()->published()->create(['quote' => 'Card without attribution', 'person_name' => 'Display name only', 'person_role' => null, 'avatar' => 'legacy-avatar.jpg', 'rating' => 5]);

    $this->get('/')->assertSee('Card without attribution')->assertSee('Display name only')
        ->assertDontSee('ولي أمر طالب')->assertDontSee('★★★★★')->assertDontSee('5 من 5 نجوم')
        ->assertDontSee('legacy-avatar.jpg');
});

test('no eligible testimonials shows the neutral empty state', function (bool $hasDrafts) {
    if ($hasDrafts) {
        Testimonial::factory()->approved()->create();
        Testimonial::factory()->create(['is_published' => true]);
    }

    $this->get('/')->assertOk()->assertViewHas('testimonials', fn ($items) => $items->isEmpty())
        ->assertSee('ستُعرض شهادات أولياء الأمور هنا بعد اعتمادها وموافقتهم على نشرها.');
})->with(['empty table' => false, 'only ineligible records' => true]);

test('homepage settings can hide and restore published testimonials', function () {
    Testimonial::factory()->published()->create(['quote' => 'Visibility controlled testimonial']);
    $settings = HomepageSetting::query()->create(['section_visibility' => ['testimonials' => false]]);

    $this->get('/')->assertOk()->assertDontSee('id="testimonials"', false)
        ->assertDontSee('Visibility controlled testimonial')
        ->assertDontSee('ستُعرض شهادات أولياء الأمور هنا بعد اعتمادها وموافقتهم على نشرها.');

    $settings->update(['section_visibility' => ['testimonials' => true]]);

    $this->get('/')->assertOk()->assertSee('id="testimonials"', false)->assertSee('Visibility controlled testimonial');
});
