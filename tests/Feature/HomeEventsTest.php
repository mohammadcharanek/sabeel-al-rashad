<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

test('homepage only shows published upcoming events', function () {
    /** @var \Tests\TestCase $this */

    $this->travelTo('2026-09-14 12:00:00');

    $publishedUpcoming = Event::factory()->create([
        'title' => 'فعالية قادمة',
        'is_published' => true,
        'starts_at' => '2026-09-20 10:00:00',
    ]);

    Event::factory()->create([
        'title' => 'فعالية غير منشورة',
        'is_published' => false,
        'starts_at' => '2026-09-20 10:00:00',
    ]);

    Event::factory()->create([
        'title' => 'فعالية قديمة',
        'is_published' => true,
        'starts_at' => '2026-09-10 10:00:00',
    ]);

    $this->get('/')
        ->assertSee($publishedUpcoming->title)
        ->assertDontSee('فعالية غير منشورة')
        ->assertDontSee('فعالية قديمة');
});

test('homepage limits events to four records', function () {
    /** @var \Tests\TestCase $this */

    $this->travelTo('2026-09-14 12:00:00');

    Event::factory()
        ->count(5)
        ->sequence(
            ['title' => 'الفعالية الأولى', 'starts_at' => '2026-09-15 10:00:00'],
            ['title' => 'الفعالية الثانية', 'starts_at' => '2026-09-16 10:00:00'],
            ['title' => 'الفعالية الثالثة', 'starts_at' => '2026-09-17 10:00:00'],
            ['title' => 'الفعالية الرابعة', 'starts_at' => '2026-09-18 10:00:00'],
            ['title' => 'الفعالية الخامسة', 'starts_at' => '2026-09-19 10:00:00'],
        )
        ->create([
            'is_published' => true,
            'sort_order' => 0,
        ]);

    $this->get('/')
        ->assertSee('الفعالية الأولى')
        ->assertSee('الفعالية الثانية')
        ->assertSee('الفعالية الثالثة')
        ->assertSee('الفعالية الرابعة')
        ->assertDontSee('الفعالية الخامسة');
});

test('homepage orders events by sort order then start date', function () {
    /** @var \Tests\TestCase $this */

    $this->travelTo('2026-09-14 12:00:00');

    $third = Event::factory()->create([
        'title' => 'الفعالية الثالثة',
        'is_published' => true,
        'sort_order' => 2,
        'starts_at' => '2026-09-15 10:00:00',
    ]);

    $second = Event::factory()->create([
        'title' => 'الفعالية الثانية',
        'is_published' => true,
        'sort_order' => 1,
        'starts_at' => '2026-09-16 10:00:00',
    ]);

    $first = Event::factory()->create([
        'title' => 'الفعالية الأولى',
        'is_published' => true,
        'sort_order' => 1,
        'starts_at' => '2026-09-15 10:00:00',
    ]);

    $this->get('/')
        ->assertSeeInOrder([
            $first->title,
            $second->title,
            $third->title,
        ]);
});

test('homepage shows empty events state when there are no upcoming published events', function () {
    /** @var \Tests\TestCase $this */

    $this->travelTo('2026-09-14 12:00:00');

    Event::factory()->create([
        'is_published' => false,
        'starts_at' => '2026-09-20 10:00:00',
    ]);

    $this->get('/')
        ->assertSee('لا توجد فعاليات قادمة حالياً.');
});