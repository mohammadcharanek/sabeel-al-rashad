<?php

use App\Models\EducationalStage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

test('homepage only shows active educational stages', function () {
    /** @var TestCase $this */
    $active = EducationalStage::factory()->create([
        'title' => 'المرحلة الابتدائية',
        'is_active' => true,
    ]);

    EducationalStage::factory()->create([
        'title' => 'مرحلة مخفية',
        'is_active' => false,
    ]);

    $this->get('/')
        ->assertSee($active->title)
        ->assertDontSee('مرحلة مخفية');
});

test('homepage orders educational stages by sort order then id', function () {
    /** @var TestCase $this */
    $third = EducationalStage::factory()->create([
        'title' => 'المرحلة الثالثة',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $second = EducationalStage::factory()->create([
        'title' => 'المرحلة الثانية',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $first = EducationalStage::factory()->create([
        'title' => 'المرحلة الأولى',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->get('/')
        ->assertSeeInOrder([
            $first->title,
            $second->title,
            $third->title,
        ]);
});

test('homepage shows stage description and custom link label', function () {
    /** @var TestCase $this */
    $stage = EducationalStage::factory()->create([
        'title' => 'المرحلة الثانوية',
        'description' => 'إعداد أكاديمي وشخصي متوازن.',
        'link_label' => 'اكتشف المرحلة',
        'link_url' => 'https://example.com/secondary',
        'is_active' => true,
    ]);

    $this->get('/')
        ->assertSee($stage->title)
        ->assertSee($stage->description)
        ->assertSee('اكتشف المرحلة')
        ->assertSee('https://example.com/secondary', false);
});

test('homepage hides stage link when no url exists', function () {
    /** @var TestCase $this */
    EducationalStage::factory()->create([
        'title' => 'المرحلة الابتدائية',
        'link_label' => 'تعرف أكثر',
        'link_url' => null,
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response
        ->assertSee('المرحلة الابتدائية')
        ->assertDontSee('تعرف أكثر');
});

test('homepage shows empty state when there are no active educational stages', function () {
    /** @var TestCase $this */
    EducationalStage::factory()->inactive()->create();

    $this->get('/')
        ->assertSee('لا توجد مراحل تعليمية متاحة حالياً.');
});

test('stage cards render canonical and legacy icons with their original artwork', function (string $icon, string $artwork) {
    $stage = EducationalStage::factory()->create();
    DB::table('educational_stages')->where('id', $stage->id)->update(['icon' => $icon]);

    $this->get('/')->assertSee($artwork, false);
})->with([
    ['kindergarten', '<circle cx="16" cy="10" r="4" />'],
    ['Kindergarten Stage', '<circle cx="16" cy="10" r="4" />'],
    ['basic', '<path d="M16 5 26 10 16 15 6 10 16 5Z" />'],
    ['Basic Education Stage', '<path d="M16 5 26 10 16 15 6 10 16 5Z" />'],
    ['primary', '<path d="M16 5 26 10 16 15 6 10 16 5Z" />'],
    ['elementary', '<path d="M16 5 26 10 16 15 6 10 16 5Z" />'],
    ['intermediate', '<rect x="6" y="7" width="20" height="18" rx="2" />'],
    ['middle', '<rect x="6" y="7" width="20" height="18" rx="2" />'],
    ['secondary', '<circle cx="15" cy="10" r="4" />'],
]);
