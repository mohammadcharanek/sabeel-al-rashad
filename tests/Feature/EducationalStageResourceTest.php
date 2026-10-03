<?php

use App\Filament\Resources\EducationalStages\Pages\CreateEducationalStage;
use App\Filament\Resources\EducationalStages\Pages\EditEducationalStage;
use App\Filament\Resources\EducationalStages\Pages\ListEducationalStages;
use App\Models\EducationalStage;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('educational stage rejects URLs that cannot fit the database or use an unsafe scheme', function (string $url, string $rule) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEducationalStage::class)
        ->fillForm([
            'title' => 'المرحلة الابتدائية',
            'slug' => 'primary',
            'category' => 'primary',
            'icon' => 'elementary',
            'link_url' => $url,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'link_url' => $rule,
        ]);

    $this->assertDatabaseCount('educational_stages', 0);
})->with([
    'over 255 characters' => [
        'https://example.com/'.str_repeat('a', 240),
        'max',
    ],
    'FTP scheme' => [
        'ftp://example.com/stage',
        'url',
    ],
]);

test('educational stage accepts a URL at the database length boundary', function () {
    $this->actingAs(User::factory()->admin()->create());

    $url = 'https://example.com/'.str_repeat('a', 235);

    Livewire::test(CreateEducationalStage::class)
        ->fillForm([
            'title' => 'مرحلة اختبار',
            'slug' => 'test-stage',
            'category' => 'primary',
            'icon' => 'elementary',
            'link_url' => $url,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('educational_stages', [
        'slug' => 'test-stage',
        'link_url' => $url,
    ]);
});

test('stage management rejects missing and unknown categories', function (?string $category) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEducationalStage::class)
        ->fillForm([
            'title' => 'مرحلة',
            'slug' => 'stage',
            'category' => $category,
            'icon' => 'elementary',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'category',
        ]);

    $this->assertDatabaseCount('educational_stages', 0);
})->with([
    'missing' => null,
    'invalid' => 'unknown',
]);

test('staff can classify existing stages and filter categories using Arabic labels', function (string $category, string $label) {
    $target = EducationalStage::factory()->create([
        'category' => null,
    ]);

    $other = EducationalStage::factory()->create([
        'category' => null,
    ]);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationalStage::class, [
        'record' => $target->id,
    ])
        ->fillForm([
            'category' => $category,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('educational_stages', [
        'id' => $target->id,
        'category' => $category,
    ]);

    Livewire::test(ListEducationalStages::class)
        ->filterTable('category', $category)
        ->assertCanSeeTableRecords([
            $target,
        ])
        ->assertCanNotSeeTableRecords([
            $other,
        ])
        ->assertSee($label);
})->with([
    ['kindergarten', 'روضات'],
    ['primary', 'ابتدائي'],
    ['intermediate', 'متوسط'],
    ['secondary', 'ثانوي'],
]);
