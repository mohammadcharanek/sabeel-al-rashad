<?php

use App\Filament\Resources\EducationalStages\Pages\CreateEducationalStage;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('educational stage rejects URLs that cannot fit the database or use an unsafe scheme', function (string $url, string $rule) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEducationalStage::class)
        ->fillForm(['title' => 'المرحلة الابتدائية', 'slug' => 'primary', 'link_url' => $url])
        ->call('create')
        ->assertHasFormErrors(['link_url' => $rule]);

    $this->assertDatabaseCount('educational_stages', 0);
})->with([
    'over 255 characters' => ['https://example.com/'.str_repeat('a', 240), 'max'],
    'FTP scheme' => ['ftp://example.com/stage', 'url'],
]);

test('educational stage accepts a URL at the database length boundary', function () {
    $this->actingAs(User::factory()->admin()->create());
    $url = 'https://example.com/'.str_repeat('a', 235);

    Livewire::test(CreateEducationalStage::class)
        ->fillForm(['title' => 'المرحلة الابتدائية', 'slug' => 'primary', 'link_url' => $url])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('educational_stages', ['slug' => 'primary', 'link_url' => $url]);
});
