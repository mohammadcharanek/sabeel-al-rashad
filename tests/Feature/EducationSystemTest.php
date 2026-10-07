<?php

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\EducationSystem;
use App\Models\StudentApplication;
use Database\Seeders\AmericanEducationSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

test('American seeding creates twelve mapped grades while preserving existing national data and IDs', function () {
    $national = EducationalGrade::factory()->create(['code' => 'grade_1']);
    $application = StudentApplication::factory()->create([
        'educational_stage_id' => $national->educational_stage_id, 'educational_grade_id' => $national->id,
    ]);
    $original = $application->refresh()->getRawOriginal();
    $stageOriginal = $national->educationalStage->getRawOriginal();

    $this->seed(AmericanEducationSeeder::class);

    expect(EducationSystem::where('slug', 'lebanese')->exists())->toBeTrue();
    $american = EducationSystem::where('slug', 'american')->sole();
    expect($american->educationalStages()->orderBy('sort_order')->pluck('category')->all())
        ->toBe(['elementary', 'middle_school', 'high_school']);
    $expected = [
        1 => 'elementary', 2 => 'elementary', 3 => 'elementary', 4 => 'elementary', 5 => 'elementary',
        6 => 'middle_school', 7 => 'middle_school', 8 => 'middle_school',
        9 => 'high_school', 10 => 'high_school', 11 => 'high_school', 12 => 'high_school',
    ];
    foreach ($expected as $number => $category) {
        $grade = EducationalGrade::where('code', 'american_grade_'.$number)->sole();
        expect($grade->educationalStage->category)->toBe($category);
        expect($grade->educationalStage->education_system_id)->toBe($american->id);
        expect($grade->sort_order)->toBe($number);
    }
    expect($application->fresh()->getRawOriginal())->toBe($original);
    expect($national->educationalStage->fresh()->getRawOriginal())->toBe($stageOriginal);
    $this->assertModelExists($national);
    $this->assertDatabaseCount('educational_grades', 13);
});

test('American seeding is idempotent and preserves administrator content and inactive statuses', function () {
    $this->seed(AmericanEducationSeeder::class);
    $system = EducationSystem::where('slug', 'american')->sole();
    $system->update(['name' => 'اسم مخصص', 'is_active' => false]);
    $stage = $system->educationalStages()->where('category', 'elementary')->sole();
    $stage->update(['title' => 'مرحلة مخصصة', 'image' => 'educational-stages/existing.jpg', 'sort_order' => 28]);
    $grade = $stage->educationalGrades()->where('code', 'american_grade_1')->sole();
    $grade->update(['name_ar' => 'صف مخصص', 'is_active' => false, 'sort_order' => 19]);
    $systems = EducationSystem::orderBy('id')->get()->map->getRawOriginal()->all();
    $stages = EducationalStage::orderBy('id')->get()->map->getRawOriginal()->all();
    $grades = EducationalGrade::orderBy('id')->get()->map->getRawOriginal()->all();

    $this->seed(AmericanEducationSeeder::class);

    expect(EducationSystem::orderBy('id')->get()->map->getRawOriginal()->all())->toBe($systems);
    expect(EducationalStage::orderBy('id')->get()->map->getRawOriginal()->all())->toBe($stages);
    expect(EducationalGrade::orderBy('id')->get()->map->getRawOriginal()->all())->toBe($grades);
});

test('American seeding refuses a conflicting slug without partial additions', function () {
    $existing = EducationalStage::factory()->create(['slug' => 'american-middle-school']);
    $original = $existing->refresh()->getRawOriginal();

    expect(fn () => $this->seed(AmericanEducationSeeder::class))->toThrow(ValidationException::class);

    expect($existing->fresh()->getRawOriginal())->toBe($original);
    $this->assertDatabaseCount('educational_stages', 1);
    $this->assertDatabaseCount('educational_grades', 0);
    expect(EducationSystem::where('slug', 'american')->exists())->toBeFalse();
});

test('system categories and grade boundaries reject incompatible model writes', function (string $category, string $code) {
    $stage = EducationalStage::factory()->american($category)->create();

    expect(fn () => EducationalGrade::factory()->for($stage, 'educationalStage')->create(['code' => $code]))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('educational_grades', 0);
})->with([
    ['elementary', 'american_grade_6'], ['elementary', 'american_grade_0'],
    ['middle_school', 'american_grade_5'], ['middle_school', 'american_grade_9'],
    ['high_school', 'american_grade_8'], ['high_school', 'american_grade_13'],
    ['elementary', 'grade_1'], ['high_school', 'kg1'],
]);

test('a national stage rejects American categories', function () {
    expect(fn () => EducationalStage::factory()->create(['category' => 'high_school']))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('educational_stages', 0);
});

test('referenced system and stage identities remain stable for historical applications', function () {
    $grade = EducationalGrade::factory()->american(12)->create();
    $stage = $grade->educationalStage;
    $system = $stage->educationSystem;
    $application = StudentApplication::factory()->create(['educational_grade_id' => $grade->id, 'educational_stage_id' => $stage->id]);
    $other = EducationSystem::factory()->create();

    expect(fn () => $system->update(['slug' => 'renamed']))->toThrow(ValidationException::class);
    expect(fn () => $stage->update(['education_system_id' => $other->id]))->toThrow(ValidationException::class);
    expect($system->fresh()->delete())->toBeFalse();
    expect($stage->fresh()->delete())->toBeFalse();

    $this->assertModelExists($application);
    expect($stage->fresh()->education_system_id)->toBe($system->id);
});

test('an unused education system can be deleted', function () {
    $system = EducationSystem::factory()->create();

    expect($system->delete())->toBeTrue();

    $this->assertModelMissing($system);
});

test('homepage separates systems and hides inactive systems without losing legacy icons', function () {
    $national = EducationalStage::factory()->create(['title' => 'مرحلة وطنية']);
    $american = EducationalStage::factory()->american('high_school')->create(['title' => 'مرحلة أميركية']);
    DB::table('educational_stages')->where('id', $national->id)->update(['icon' => 'elementary']);

    $this->get('/')->assertSeeInOrder(['المنهج اللبناني', 'مرحلة وطنية', 'المنهج الأميركي', 'مرحلة أميركية'])
        ->assertSee('<path d="M16 5 26 10 16 15 6 10 16 5Z" />', false)
        ->assertSee('<circle cx="15" cy="10" r="4" />', false);
    $american->educationSystem->update(['is_active' => false]);
    $this->get('/')->assertSee($national->title)->assertDontSee($american->title);
});

test('American seeding refuses a grade code assigned to a different stage without partial additions', function () {
    $existing = EducationalGrade::factory()->american(6)->create();
    $stages = EducationalStage::orderBy('id')->get()->map->getRawOriginal()->all();
    $grades = EducationalGrade::orderBy('id')->get()->map->getRawOriginal()->all();

    expect(fn () => $this->seed(AmericanEducationSeeder::class))->toThrow(ValidationException::class);

    expect(EducationalStage::orderBy('id')->get()->map->getRawOriginal()->all())->toBe($stages);
    expect(EducationalGrade::orderBy('id')->get()->map->getRawOriginal()->all())->toBe($grades);
    $this->assertModelExists($existing);
});

test('American grade codes are globally unique across stages', function () {
    EducationalGrade::factory()->american(1)->create();

    expect(fn () => EducationalGrade::factory()->american(1)->create())->toThrow(QueryException::class);

    $this->assertDatabaseCount('educational_grades', 1);
});

test('American factories reject grades outside the supported range', function (int $number) {
    expect(fn () => EducationalGrade::factory()->american($number)->create())->toThrow(InvalidArgumentException::class);

    $this->assertDatabaseCount('educational_grades', 0);
})->with([0, 13]);
