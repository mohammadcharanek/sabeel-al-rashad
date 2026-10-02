<?php

use App\Filament\Resources\EducationalGrades\Pages\CreateEducationalGrade;
use App\Filament\Resources\EducationalGrades\Pages\EditEducationalGrade;
use App\Filament\Resources\EducationalGrades\Pages\ListEducationalGrades;
use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\StudentApplication;
use App\Models\User;
use Database\Seeders\EducationalGradeSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('administrators manage grades with stable codes and Arabic fields', function () {
    $stage = EducationalStage::factory()->create(['category' => 'kindergarten']);
    $this->actingAs(User::factory()->admin()->create());
    Livewire::test(CreateEducationalGrade::class)->fillForm([
        'name_ar' => 'الروضة الأولى', 'code' => 'kg1', 'educational_stage_id' => $stage->id,
        'sort_order' => 1, 'is_active' => true,
    ])->call('create')->assertHasNoFormErrors();
    $grade = EducationalGrade::sole();
    expect($grade->educationalStage->is($stage))->toBeTrue();
    Livewire::test(EditEducationalGrade::class, ['record' => $grade->id])
        ->fillForm(['sort_order' => 5, 'is_active' => false])->call('save')->assertHasNoFormErrors();
    expect($grade->refresh()->is_active)->toBeFalse();
    expect($grade->sort_order)->toBe(5);
    Livewire::test(ListEducationalGrades::class)->filterTable('educational_stage_id', $stage->id)->assertCanSeeTableRecords([$grade])->assertSee('الروضة الأولى');
});

test('grade codes cannot be duplicated or assigned to the wrong stage category', function () {
    $grade = EducationalGrade::factory()->create(['code' => 'grade_1']);
    $this->actingAs(User::factory()->admin()->create());
    Livewire::test(CreateEducationalGrade::class)->fillForm([
        'name_ar' => 'صف آخر', 'code' => 'grade_1', 'educational_stage_id' => $grade->educational_stage_id,
    ])->call('create')->assertHasFormErrors(['code']);
    Livewire::test(CreateEducationalGrade::class)->fillForm([
        'name_ar' => 'صف آخر', 'code' => 'kg1', 'educational_stage_id' => $grade->educational_stage_id,
    ])->call('create')->assertHasFormErrors(['code']);
    expect(fn () => EducationalGrade::factory()->create(['code' => 'kg1']))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('educational_grades', 1);
});

test('referenced grade identity and stage classification cannot be changed and deletion is restricted', function () {
    $grade = EducationalGrade::factory()->create();
    $application = StudentApplication::factory()->create(['educational_grade_id' => $grade->id, 'educational_stage_id' => $grade->educational_stage_id]);
    $admin = User::factory()->admin()->create();
    expect(Gate::forUser($admin)->allows('delete', $grade))->toBeFalse();
    foreach (['name_ar' => 'اسم مختلف', 'code' => 'grade_99999', 'educational_stage_id' => EducationalStage::factory()->create()->id] as $field => $value) {
        expect(fn () => $grade->fresh()->update([$field => $value]))->toThrow(ValidationException::class);
    }
    expect(fn () => $grade->educationalStage->update(['category' => 'secondary']))->toThrow(ValidationException::class);
    expect(fn () => $grade->delete())->toThrow(QueryException::class);
    $this->assertModelExists($application);
    $this->assertModelExists($grade);
    $this->actingAs($admin);
    Livewire::test(EditEducationalGrade::class, ['record' => $grade->id])
        ->set('data.code', 'grade_99999')->fillForm(['sort_order' => 9])->call('save')->assertHasNoFormErrors();
    expect($grade->refresh()->code)->not->toBe('grade_99999');
    expect($grade->sort_order)->toBe(9);
});

test('non administrators cannot manage grades', function () {
    $this->actingAs(User::factory()->create());
    Livewire::test(ListEducationalGrades::class)->assertForbidden();
    Livewire::test(CreateEducationalGrade::class)->assertForbidden();
});

test('kindergarten seeder preserves existing values and is idempotent without relying on stage IDs', function () {
    $stage = EducationalStage::factory()->create(['id' => 42, 'category' => 'kindergarten']);
    $existing = EducationalGrade::factory()->for($stage, 'educationalStage')->create([
        'code' => 'kg1', 'name_ar' => 'اسم معتمد', 'sort_order' => 17, 'is_active' => false,
    ]);
    $original = $existing->refresh()->getRawOriginal();
    $stageOriginal = $stage->refresh()->getRawOriginal();
    $this->seed(EducationalGradeSeeder::class);
    $this->seed(EducationalGradeSeeder::class);
    expect($existing->refresh()->getRawOriginal())->toBe($original);
    expect($stage->refresh()->getRawOriginal())->toBe($stageOriginal);
    expect(EducationalGrade::orderBy('code')->pluck('code')->all())->toBe(['kg1', 'kg2', 'kg3']);
    expect(EducationalGrade::pluck('educational_stage_id')->unique()->all())->toBe([42]);
});

test('kindergarten seeder does not guess when the stage is missing or ambiguous', function (int $stageCount) {
    EducationalStage::factory()->count($stageCount)->create(['category' => 'kindergarten']);
    $other = EducationalGrade::factory()->create();
    $original = $other->refresh()->getRawOriginal();
    $this->seed(EducationalGradeSeeder::class);
    $this->assertDatabaseCount('educational_grades', 1);
    expect($other->refresh()->getRawOriginal())->toBe($original);
})->with([0, 2]);

test('grade migrations and rollback preserve historical application fields and private document references', function () {
    DB::connection()->getPdo();
    $originalConnection = DB::getDefaultConnection();
    config(['database.connections.grade_upgrade' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]]);
    DB::setDefaultConnection('grade_upgrade');
    try {
        foreach (['2026_09_14_113313_create_educational_stages_table.php', '2026_09_26_194452_create_student_applications_table.php',
            '2026_10_01_230556_add_category_to_educational_stages_table.php', '2026_10_01_230558_add_registration_type_to_student_applications_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        $application = StudentApplication::factory()->create(['document_path' => 'documents/'.str_repeat('a', 40).'.pdf', 'admin_note' => 'ملاحظة قديمة']);
        $original = $application->refresh()->getRawOriginal();
        $stageOriginal = $application->educationalStage->getRawOriginal();
        $grades = require database_path('migrations/2026_10_02_222157_create_educational_grades_table.php');
        $reference = require database_path('migrations/2026_10_02_222158_add_educational_grade_id_to_student_applications_table.php');
        $grades->up();
        $reference->up();
        expect($application->refresh()->educational_grade_id)->toBeNull();
        expect(collect($application->refresh()->getRawOriginal())->except('educational_grade_id')->all())->toBe($original);
        expect($application->educationalStage->getRawOriginal())->toBe($stageOriginal);
        $reference->down();
        $grades->down();
        expect($application->refresh()->getRawOriginal())->toBe($original);
        expect(Schema::hasTable('educational_grades'))->toBeFalse();
    } finally {
        DB::setDefaultConnection($originalConnection);
        DB::purge('grade_upgrade');
    }
});
