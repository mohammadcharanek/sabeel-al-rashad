<?php

use App\Filament\Pages\SiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'production']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('settings can be read before configuration without creating a record', function () {
    $settings = SiteSetting::current();

    expect($settings->exists)->toBeFalse();
    expect($settings->school_name_ar)->toBe('ثانوية سبيل الرشاد');
    expect($settings->whatsapp_enabled)->toBeFalse();
    $this->assertDatabaseCount('site_settings', 0);
});

test('administrators save and reopen the same global settings including normalized phone and image', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(SiteSettings::class)
        ->fillForm([
            'school_name_ar' => 'ثانوية سبيل الرشاد',
            'contact_email' => 'school@example.com',
            'facebook_url' => 'https://example.com/school',
            'whatsapp_enabled' => true,
            'whatsapp_number' => '00 961 (70) 123-456',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $settings = SiteSetting::current();
    $id = $settings->id;
    expect($settings->whatsapp_number)->toBe('+96170123456');
    expect($settings->logo)->toStartWith('settings/');
    Storage::disk('public')->assertExists($settings->logo);

    Livewire::test(SiteSettings::class)
        ->assertSchemaStateSet(['contact_email' => 'school@example.com'])
        ->fillForm(['school_name_en' => 'Sabeel Al Rashad'])
        ->set('data.id', 999)
        ->set('data.singleton_key', 'forged')
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseCount('site_settings', 1);
    $this->assertDatabaseHas('site_settings', ['id' => $id, 'singleton_key' => 'global', 'school_name_en' => 'Sabeel Al Rashad']);
    Storage::disk('public')->assertExists($settings->logo);
});

test('two initially empty settings editors still manage one record', function () {
    $this->actingAs(User::factory()->admin()->create());
    $first = Livewire::test(SiteSettings::class);
    $second = Livewire::test(SiteSettings::class);

    $first->fillForm(['school_name_ar' => 'الاسم الأول'])->call('save')->assertHasNoFormErrors();
    $second->fillForm(['school_name_ar' => 'الاسم الثاني'])->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseCount('site_settings', 1);
    $this->assertDatabaseHas('site_settings', ['school_name_ar' => 'الاسم الثاني']);
});

test('settings validation rejects invalid values without saving', function (string $field, mixed $value, string $rule) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(SiteSettings::class)
        ->fillForm([$field => $value])
        ->call('save')
        ->assertHasFormErrors([$field => $rule])
        ->assertNotNotified();

    $this->assertDatabaseCount('site_settings', 0);
})->with([
    'required school name' => ['school_name_ar', '', 'required'],
    'invalid contact email' => ['contact_email', 'invalid-address', 'email'],
    'invalid notification email' => ['notification_email', 'invalid-address', 'email'],
    'javascript URL' => ['facebook_url', 'javascript:alert(1)', 'url'],
    'FTP URL' => ['instagram_url', 'ftp://example.com/photo', 'url'],
    'oversize URL' => ['youtube_url', 'https://example.com/'.str_repeat('a', 2048), 'max'],
    'invalid phone' => ['whatsapp_number', '+961ABC123456', 'regex'],
    'long international phone' => ['whatsapp_number', '+1234567890123456', 'regex'],
]);

test('enabling WhatsApp requires a number', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(SiteSettings::class)
        ->fillForm(['whatsapp_enabled' => true, 'whatsapp_number' => null])
        ->call('save')
        ->assertHasFormErrors(['whatsapp_number' => 'required_if']);

    $this->assertDatabaseCount('site_settings', 0);
});

test('unsafe images are rejected without creating settings', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(SiteSettings::class)
        ->fillForm(['logo' => UploadedFile::fake()->create('payload.svg', 1, 'image/svg+xml')])
        ->call('save')
        ->assertHasFormErrors(['logo']);

    $this->assertDatabaseCount('site_settings', 0);
    expect(Storage::disk('public')->allFiles('settings'))->toBeEmpty();
});

test('submitted existing image paths cannot attach unrelated files', function () {
    Storage::fake('public');
    Storage::disk('public')->put('settings/unrelated.jpg', 'existing file');
    $this->actingAs(User::factory()->admin()->create());
    SiteSetting::factory()->create();

    Livewire::test(SiteSettings::class)
        ->set('data.logo', ['forged' => 'settings/unrelated.jpg'])
        ->call('save')
        ->assertHasFormErrors(['logo']);

    expect(SiteSetting::current()->logo)->toBeNull();
    Storage::disk('public')->assertExists('settings/unrelated.jpg');
});

test('non administrators cannot mount the settings editor', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(SiteSettings::class)->assertForbidden();

    $this->assertDatabaseCount('site_settings', 0);
});

test('settings save rechecks authorization after access is revoked', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);
    $component = Livewire::test(SiteSettings::class)->fillForm(['school_name_ar' => 'تعديل مرفوض']);
    $user->is_admin = false;
    $user->save();
    $this->actingAs($user->fresh());

    $component->call('save')->assertForbidden();

    $this->assertDatabaseCount('site_settings', 0);
});
