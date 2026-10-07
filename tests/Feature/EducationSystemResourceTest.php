<?php

use App\Filament\Resources\EducationSystems\Pages\CreateEducationSystem;
use App\Filament\Resources\EducationSystems\Pages\EditEducationSystem;
use App\Filament\Resources\EducationSystems\Pages\ListEducationSystems;
use App\Models\EducationalStage;
use App\Models\EducationSystem;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('administrators create edit list and filter education systems', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEducationSystem::class)->fillForm([
        'name' => 'المنهج الأميركي', 'slug' => 'american', 'description' => 'وصف النظام',
        'sort_order' => 3, 'is_active' => true,
    ])->call('create')->assertHasNoFormErrors();

    $system = EducationSystem::where('slug', 'american')->sole();
    expect($system->description)->toBe('وصف النظام');
    Livewire::test(EditEducationSystem::class, ['record' => $system->id])
        ->fillForm(['name' => 'منهج محدث', 'sort_order' => 7, 'is_active' => false])->call('save')->assertHasNoFormErrors();
    $this->assertDatabaseHas('education_systems', ['id' => $system->id, 'name' => 'منهج محدث', 'sort_order' => 7, 'is_active' => false]);
    Livewire::test(ListEducationSystems::class)->filterTable('is_active', false)
        ->assertCanSeeTableRecords([$system])->assertTableColumnStateSet('educational_stages_count', 0, $system);
    Livewire::test(ListEducationSystems::class)->filterTable('is_active', true)->assertCanNotSeeTableRecords([$system]);
});

test('system forms reject missing invalid and duplicate values', function (array $data, array $errors) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEducationSystem::class)->fillForm(array_replace([
        'name' => 'منهج', 'slug' => 'custom-system', 'sort_order' => 0,
    ], $data))->call('create')->assertHasFormErrors($errors);

    $this->assertDatabaseCount('education_systems', 1);
})->with([
    'required' => [['name' => null, 'slug' => null, 'sort_order' => null], ['name' => 'required', 'slug' => 'required', 'sort_order' => 'required']],
    'duplicate' => [['slug' => 'lebanese'], ['slug' => 'unique']],
    'invalid slug' => [['slug' => 'American System'], ['slug' => 'regex']],
    'negative order' => [['sort_order' => -1], ['sort_order' => 'min']],
]);

test('referenced systems retain their slug and refuse deletion with an Arabic explanation', function () {
    $stage = EducationalStage::factory()->create();
    $system = $stage->educationSystem;
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationSystem::class, ['record' => $system->id])
        ->set('data.slug', 'changed')->fillForm(['name' => 'اسم محدث'])->call('save')->assertHasNoFormErrors()
        ->callAction(DeleteAction::class)
        ->assertNotified(Notification::make()->danger()->title('تعذر حذف نظام التعليم')
            ->body('لا يمكن حذف نظام التعليم لأنه يحتوي على مراحل تعليمية مرتبطة به.'));

    expect($system->fresh()->slug)->toBe('lebanese');
    $this->assertModelExists($stage);
});

test('an administrator can delete an unused system', function () {
    $system = EducationSystem::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationSystem::class, ['record' => $system->id])->callAction(DeleteAction::class)->assertNotified();

    $this->assertModelMissing($system);
});

test('mixed bulk system deletion leaves every selected system intact', function () {
    $free = EducationSystem::factory()->create();
    $stage = EducationalStage::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListEducationSystems::class)->callTableBulkAction('delete', [$free, $stage->educationSystem])
        ->assertNotified('تعذر حذف أنظمة التعليم المحددة');

    $this->assertModelExists($free);
    $this->assertModelExists($stage->educationSystem);
    $this->assertModelExists($stage);
});

test('bulk system deletion removes only selected unused systems', function () {
    $systems = EducationSystem::factory()->count(2)->create();
    $unselected = EducationSystem::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListEducationSystems::class)->callTableBulkAction('delete', $systems)->assertNotified();

    foreach ($systems as $system) {
        $this->assertModelMissing($system);
    }
    $this->assertModelExists($unselected);
});

test('education system management is restricted to administrators', function () {
    $system = EducationSystem::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::forUser($user)->allows($ability, $system))->toBeFalse();
    }
    foreach (['viewAny', 'create', 'deleteAny'] as $ability) {
        expect(Gate::forUser($user)->allows($ability, EducationSystem::class))->toBeFalse();
    }
    Livewire::test(ListEducationSystems::class)->assertForbidden();
    Livewire::test(CreateEducationSystem::class)->assertForbidden();
    Livewire::test(EditEducationSystem::class, ['record' => $system->id])->assertForbidden();
});
