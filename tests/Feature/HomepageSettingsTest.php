<?php

use App\Filament\Pages\HomepageSettings;
use App\Models\HomepageSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'production']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('homepage settings default to visible sections without creating a record', function () {
    expect(HomepageSetting::current()->isSectionVisible('news'))->toBeTrue();
    $this->assertDatabaseCount('homepage_settings', 0);
});

test('administrators edit one homepage settings record and publish saved content', function () {
    $this->actingAs(User::factory()->admin()->create());
    $first = Livewire::test(HomepageSettings::class);
    $second = Livewire::test(HomepageSettings::class);

    $first->fillForm([
        'hero_title' => 'عنوان محفوظ للمرحلة التالية',
        'hero_primary_url' => 'https://example.com/admissions',
        'section_visibility.news' => false,
    ])->call('save')->assertHasNoFormErrors()->assertNotified();

    $id = HomepageSetting::current()->id;
    expect(HomepageSetting::current()->isSectionVisible('news'))->toBeFalse();

    $this->get('/')->assertSee('عنوان محفوظ للمرحلة التالية')->assertDontSee('id="news"', false);

    $second->fillForm(['intro_title' => 'مقدمة المدرسة'])->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseCount('homepage_settings', 1);
    $this->assertDatabaseHas('homepage_settings', ['id' => $id, 'intro_title' => 'مقدمة المدرسة']);
    $this->get('/')->assertOk()->assertSee('مقدمة المدرسة')->assertDontSee('عنوان محفوظ للمرحلة التالية');
});

test('homepage URL validation matches the existing column and limits schemes', function (string $url, string $rule) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(HomepageSettings::class)
        ->fillForm(['hero_primary_url' => $url])
        ->call('save')
        ->assertHasFormErrors(['hero_primary_url' => $rule]);

    $this->assertDatabaseCount('homepage_settings', 0);
})->with([
    'over 255 characters' => ['https://example.com/'.str_repeat('a', 240), 'max'],
    'unsafe scheme' => ['javascript:alert(1)', 'url'],
]);

test('non administrators cannot save homepage settings after authorization changes', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);
    $component = Livewire::test(HomepageSettings::class)->fillForm(['hero_title' => 'تعديل مرفوض']);
    $user->is_admin = false;
    $user->save();
    $this->actingAs($user->fresh());

    $component->call('save')->assertForbidden();

    $this->assertDatabaseCount('homepage_settings', 0);
});
