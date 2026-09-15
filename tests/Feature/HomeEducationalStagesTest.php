<?php

use App\Models\EducationalStage;

test('homepage only shows active educational stages', function () {
    /** @var \Tests\TestCase $this */

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
    /** @var \Tests\TestCase $this */

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
    /** @var \Tests\TestCase $this */

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
    /** @var \Tests\TestCase $this */

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
    /** @var \Tests\TestCase $this */

    EducationalStage::factory()->inactive()->create();

    $this->get('/')
        ->assertSee('لا توجد مراحل تعليمية متاحة حالياً.');
});