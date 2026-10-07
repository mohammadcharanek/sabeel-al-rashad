<?php

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\StudentApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

test('stages and compatible grades can be saved for every national category', function (string $category) {
    $stage = EducationalStage::factory()->create(['category' => $category]);
    $grade = EducationalGrade::factory()->for($stage, 'educationalStage')
        ->create(['code' => $category === 'kindergarten' ? 'kg1' : 'grade_1']);

    $this->assertDatabaseHas('educational_stages', ['id' => $stage->id, 'category' => $category]);
    expect($stage->educationalGrades->sole()->is($grade))->toBeTrue();
})->with(array_keys(array_diff_key(EducationalStage::CATEGORIES, EducationalStage::AMERICAN_GRADE_RANGES)));

test('model writes reject unknown stage categories', function () {
    $stage = EducationalStage::factory()->create();

    expect(fn () => $stage->update(['category' => 'unknown']))->toThrow(ValidationException::class);

    expect($stage->fresh()->category)->toBe('basic');
    expect(fn () => EducationalStage::factory()->create(['category' => 'unknown']))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('educational_stages', 1);
});

test('legacy uncategorized stages retain content until classified', function () {
    $stage = EducationalStage::factory()->create(['category' => null, 'image' => 'educational-stages/existing.jpg']);

    $stage->update(['title' => 'مرحلة محدثة']);

    $this->assertDatabaseHas('educational_stages', [
        'id' => $stage->id, 'category' => null, 'title' => 'مرحلة محدثة', 'image' => 'educational-stages/existing.jpg',
    ]);
});

test('direct model deletion refuses stages with dependent records', function (string $dependency) {
    $linked = $dependency === 'grade' ? EducationalGrade::factory()->create() : StudentApplication::factory()->create();
    $stage = $linked->educationalStage;

    expect($stage->delete())->toBeFalse();

    $this->assertModelExists($stage);
    $this->assertModelExists($linked);
})->with(['grade', 'application']);

test('grades cannot be reassigned to an incompatible stage', function () {
    $grade = EducationalGrade::factory()->create(['code' => 'grade_1']);
    $originalStageId = $grade->educational_stage_id;
    $kindergarten = EducationalStage::factory()->create(['category' => 'kindergarten']);

    expect(fn () => $grade->update(['educational_stage_id' => $kindergarten->id]))->toThrow(ValidationException::class);

    expect($grade->fresh()->educational_stage_id)->toBe($originalStageId);
});

test('legacy icons normalize without rewriting stored stage records', function () {
    $stage = EducationalStage::factory()->create();
    DB::table('educational_stages')->where('id', $stage->id)->update(['icon' => 'Kindergarten Stage']);

    expect($stage->fresh()->icon)->toBe('kindergarten');

    $this->assertDatabaseHas('educational_stages', ['id' => $stage->id, 'icon' => 'Kindergarten Stage']);
});
