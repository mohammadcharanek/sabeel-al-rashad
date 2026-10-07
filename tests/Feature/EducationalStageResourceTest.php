<?php

use App\Filament\Resources\EducationalStages\Pages\CreateEducationalStage;
use App\Filament\Resources\EducationalStages\Pages\EditEducationalStage;
use App\Filament\Resources\EducationalStages\Pages\ListEducationalStages;
use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\EducationSystem;
use App\Models\StudentApplication;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
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
            'icon' => 'primary',
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
            'icon' => 'primary',
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
            'icon' => 'primary',
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
    ['basic', 'التعليم الأساسي'],
    ['primary', 'ابتدائي'],
    ['intermediate', 'متوسط'],
    ['secondary', 'ثانوي'],
]);

test('stage deletion explains attached grades and preserves both records', function () {
    $grade = EducationalGrade::factory()->create();
    $stage = $grade->educationalStage;
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationalStage::class, ['record' => $stage->id])
        ->callAction(DeleteAction::class)
        ->assertNotified(Notification::make()->danger()->title('تعذر حذف المرحلة')
            ->body('لا يمكن حذف المرحلة لأنها تحتوي على صفوف مرتبطة بها. يرجى حذف الصفوف أو نقلها إلى مرحلة أخرى أولاً.'));

    $this->assertModelExists($stage);
    $this->assertModelExists($grade);
});

test('stage deletion preserves historical applications without grades', function () {
    $application = StudentApplication::factory()->create();
    $stage = $application->educationalStage;
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationalStage::class, ['record' => $stage->id])
        ->callAction(DeleteAction::class)
        ->assertNotified(Notification::make()->danger()->title('تعذر حذف المرحلة')
            ->body('لا يمكن حذف المرحلة لارتباطها بطلبات تسجيل. يمكن إخفاؤها بدلاً من حذفها للحفاظ على بيانات التسجيل.'));

    $this->assertModelExists($stage);
    $this->assertModelExists($application);
});

test('an unreferenced stage can be deleted', function () {
    $stage = EducationalStage::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationalStage::class, ['record' => $stage->id])
        ->callAction(DeleteAction::class)->assertNotified();

    $this->assertModelMissing($stage);
});

test('bulk deletion refuses the entire selection when any stage has dependencies', function (string $dependency) {
    $free = EducationalStage::factory()->create();
    $linked = $dependency === 'grade' ? EducationalGrade::factory()->create() : StudentApplication::factory()->create();
    $stage = $linked->educationalStage;
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListEducationalStages::class)
        ->callTableBulkAction('delete', [$free, $stage])
        ->assertNotified('تعذر حذف المراحل المحددة');

    $this->assertModelExists($free);
    $this->assertModelExists($stage);
    $this->assertModelExists($linked);
})->with(['grade', 'application']);

test('bulk deletion removes only selected unreferenced stages', function () {
    $stages = EducationalStage::factory()->count(2)->create();
    $unselected = EducationalStage::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListEducationalStages::class)->callTableBulkAction('delete', $stages)->assertNotified();

    foreach ($stages as $stage) {
        $this->assertModelMissing($stage);
    }
    $this->assertModelExists($unselected);
});

test('editing legacy icons keeps a valid selection and saves a stable value', function (string $legacy, string $canonical) {
    $stage = EducationalStage::factory()->create();
    DB::table('educational_stages')->where('id', $stage->id)->update(['icon' => $legacy]);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationalStage::class, ['record' => $stage->id])
        ->assertFormSet(['icon' => $canonical])->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseHas('educational_stages', ['id' => $stage->id, 'icon' => $canonical]);
})->with([
    ['Kindergarten Stage', 'kindergarten'], ['Basic Education Stage', 'basic'],
    ['elementary', 'primary'], ['middle', 'intermediate'],
]);

test('stage management rejects an unknown icon', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEducationalStage::class)
        ->fillForm(['title' => 'مرحلة', 'slug' => 'stage', 'category' => 'basic', 'icon' => 'unknown'])
        ->call('create')->assertHasFormErrors(['icon']);

    $this->assertDatabaseCount('educational_stages', 0);
});

test('administrators create American stages and can change the system of an unused stage', function () {
    $system = EducationSystem::factory()->american()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEducationalStage::class)->fillForm([
        'title' => 'Middle School', 'slug' => 'american-middle', 'education_system_id' => $system->id,
        'category' => 'middle_school', 'icon' => 'middle_school',
    ])->call('create')->assertHasNoFormErrors();

    $stage = EducationalStage::sole();
    expect($stage->educationSystem->is($system))->toBeTrue();
    $national = EducationSystem::where('slug', 'lebanese')->sole();
    Livewire::test(EditEducationalStage::class, ['record' => $stage->id])
        ->set('data.education_system_id', $national->id)->assertFormSet(['category' => null])
        ->fillForm(['category' => 'intermediate'])->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseHas('educational_stages', ['id' => $stage->id, 'education_system_id' => $national->id, 'category' => 'intermediate']);
});

test('stage creation rejects incompatible categories and unavailable systems', function (string $invalid) {
    $system = EducationSystem::factory()->american()->create();
    $this->actingAs(User::factory()->admin()->create());
    if ($invalid === 'inactive') {
        $system->update(['is_active' => false]);
    }

    Livewire::test(CreateEducationalStage::class)->fillForm([
        'title' => 'مرحلة', 'slug' => 'stage', 'icon' => 'primary',
        'education_system_id' => $invalid === 'missing' ? null : ($invalid === 'unknown' ? 999999 : $system->id),
        'category' => $invalid === 'category' ? 'primary' : 'elementary',
    ])->call('create')->assertHasFormErrors([$invalid === 'category' ? 'category' : 'education_system_id']);

    $this->assertDatabaseCount('educational_stages', 0);
})->with(['category', 'inactive', 'missing', 'unknown']);

test('stages in an inactive system remain editable without changing their system', function () {
    $stage = EducationalStage::factory()->american()->create();
    $stage->educationSystem->update(['is_active' => false]);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationalStage::class, ['record' => $stage->id])
        ->fillForm(['title' => 'مرحلة محدثة'])->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseHas('educational_stages', ['id' => $stage->id, 'title' => 'مرحلة محدثة', 'education_system_id' => $stage->education_system_id]);
});

test('stage forms protect system and category when grades or historical applications exist', function (string $dependency) {
    $linked = $dependency === 'grade' ? EducationalGrade::factory()->create() : StudentApplication::factory()->create();
    $stage = $linked->educationalStage;
    $american = EducationSystem::factory()->american()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditEducationalStage::class, ['record' => $stage->id])
        ->assertFormFieldIsDisabled('education_system_id')->assertFormFieldIsDisabled('category')
        ->set('data.education_system_id', $american->id)->set('data.category', 'elementary')
        ->fillForm(['title' => 'اسم محدث'])->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseHas('educational_stages', [
        'id' => $stage->id, 'education_system_id' => $stage->education_system_id, 'category' => $stage->category, 'title' => 'اسم محدث',
    ]);
    $this->assertModelExists($linked);
})->with(['grade', 'application']);
