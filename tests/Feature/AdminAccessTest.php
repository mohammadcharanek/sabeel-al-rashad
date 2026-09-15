<?php

use App\Models\EducationalStage;
use App\Models\Event;
use App\Models\NewsPost;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    config(['app.env' => 'production']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

dataset('cms routes', [
    '/admin',
    '/admin/news-posts',
    '/admin/events',
    '/admin/educational-stages',
    '/admin/site-settings',
    '/admin/homepage-settings',
]);

test('guests are redirected to the admin login', function (string $path) {
    $this->get($path)->assertRedirect(route('filament.admin.auth.login'));
})->with('cms routes');

test('non administrators are forbidden from the CMS even with forged admin input', function (string $path) {
    $user = User::factory()->create();
    $this->actingAs($user)->withCookie('is_admin', '1');

    $this->get($path.'?is_admin=1')->assertForbidden();
})->with('cms routes');

test('an unverified administrator can access the production CMS', function (string $path) {
    $this->actingAs(User::factory()->admin()->unverified()->create());

    $this->get($path)->assertOk();
})->with('cms routes');

test('public homepage and admin login remain accessible to guests', function () {
    $this->get('/')->assertOk();
    $this->get('/admin/login')->assertOk();
});

test('admin access is limited to the intended panel and cannot be mass assigned', function () {
    $user = User::factory()->create();
    $user->fill(['is_admin' => true])->save();

    expect($user->fresh()->is_admin)->toBeFalse();
    expect(User::factory()->admin()->make()->canAccessPanel(Panel::make()->id('other')))->toBeFalse();
});

test('content policies and settings gate enforce the administrator flag', function (bool $isAdmin) {
    $user = User::factory()->create(['is_admin' => $isAdmin]);

    foreach ([NewsPost::class, Event::class, EducationalStage::class] as $model) {
        $record = $model::factory()->create();
        foreach (['viewAny', 'create', 'deleteAny'] as $ability) {
            expect(Gate::forUser($user)->allows($ability, $model))->toBe($isAdmin);
        }
        foreach (['view', 'update', 'delete'] as $ability) {
            expect(Gate::forUser($user)->allows($ability, $record))->toBe($isAdmin);
        }
    }

    expect(Gate::forUser($user)->allows('manage-settings'))->toBe($isAdmin);
})->with(['administrator' => true, 'non administrator' => false]);
