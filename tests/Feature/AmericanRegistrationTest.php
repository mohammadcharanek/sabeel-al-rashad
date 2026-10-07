<?php

use App\Models\EducationalGrade;
use App\Models\EducationSystem;
use App\Models\StudentApplication;
use Database\Seeders\AmericanEducationSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('American grades accept applications with the stage and system inferred from the validated grade', function (int $number) {
    Storage::fake('student_documents');
    $grade = EducationalGrade::factory()->american($number)->create();
    $stage = $grade->educationalStage;

    $this->post(route('registration.store'), registrationData([
        'education_system_id' => $stage->education_system_id, 'educational_grade_id' => $grade->id,
        'educational_stage_id' => 999999, 'registration_type' => 'new_student',
    ]))->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->educational_grade_id)->toBe($grade->id);
    expect($application->educational_stage_id)->toBe($stage->id);
    expect($application->document_required)->toBeFalse();
    expect($application->entrance_exam_required)->toBeFalse();
    expect($application->interview_required)->toBeTrue();
    expect($application->document_path)->toBeNull();
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with([1, 5, 6, 8, 9, 10, 12]);

test('American registration rejects inactive unclassified and mismatched relationships on the server', function (string $invalid) {
    Storage::fake('student_documents');
    $grade = EducationalGrade::factory()->american()->create();
    $stage = $grade->educationalStage;
    $systemId = $stage->education_system_id;
    match ($invalid) {
        'system' => $stage->educationSystem->update(['is_active' => false]),
        'stage' => $stage->update(['is_active' => false]),
        'grade' => $grade->update(['is_active' => false]),
        'category' => DB::table('educational_stages')->where('id', $stage->id)->update(['category' => null]),
        'national category' => DB::table('educational_stages')->where('id', $stage->id)->update(['category' => 'primary']),
        'range' => DB::table('educational_grades')->where('id', $grade->id)->update(['code' => 'american_grade_12']),
        'missing system' => DB::table('educational_stages')->where('id', $stage->id)->update(['education_system_id' => null]),
    };

    $this->get(route('registration.create'))->assertDontSee($grade->name_ar);
    $this->post(route('registration.store'), registrationData([
        'education_system_id' => $systemId, 'educational_grade_id' => $grade->id,
        'document' => UploadedFile::fake()->image('certificate.png'),
    ]))->assertSessionHasErrors('educational_grade_id');

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with(['system', 'stage', 'grade', 'category', 'national category', 'range', 'missing system']);

test('a grade from another education system cannot be submitted', function (bool $americanGrade) {
    $american = EducationalGrade::factory()->american()->create();
    $national = EducationalGrade::factory()->create(['code' => 'grade_1']);
    $grade = $americanGrade ? $american : $national;
    $systemId = ($americanGrade ? $national : $american)->educationalStage->education_system_id;

    $this->post(route('registration.store'), registrationData([
        'education_system_id' => $systemId, 'educational_grade_id' => $grade->id,
    ]))->assertSessionHasErrors('educational_grade_id');

    $this->assertDatabaseCount('student_applications', 0);
})->with([true, false]);

test('a system selection is required and malformed values are rejected', function (mixed $id) {
    $grade = EducationalGrade::factory()->american()->create();

    $this->post(route('registration.store'), registrationData([
        'education_system_id' => $id, 'educational_grade_id' => $grade->id,
    ]))->assertSessionHasErrors('education_system_id');

    $this->assertDatabaseCount('student_applications', 0);
})->with(['missing' => null, 'unknown' => 999999, 'array' => [[1]]]);

test('registration orders systems and all twelve American grades and renders system selectors', function () {
    $this->seed(AmericanEducationSeeder::class);
    $national = EducationalGrade::factory()->create(['name_ar' => 'صف لبناني']);
    EducationSystem::where('slug', 'american')->first()->update(['sort_order' => 0]);
    $national->educationalStage->educationSystem->update(['sort_order' => 2]);

    $this->get(route('registration.create'))
        ->assertSee('name="education_system_id"', false)->assertDontSee('name="educational_stage_id"', false)
        ->assertViewHas('grades', fn ($grades): bool => $grades->pluck('code')->all() === [
            'american_grade_1', 'american_grade_2', 'american_grade_3', 'american_grade_4', 'american_grade_5',
            'american_grade_6', 'american_grade_7', 'american_grade_8', 'american_grade_9', 'american_grade_10',
            'american_grade_11', 'american_grade_12', $national->code,
        ]);
});

test('American admission types retain document and exam requirements', function (string $type, bool $examRequired) {
    Storage::fake('student_documents');
    $grade = EducationalGrade::factory()->american(12)->create();
    $data = registrationData(['educational_grade_id' => $grade->id, 'registration_type' => $type]);

    $this->post(route('registration.store'), $data)->assertSessionHasErrors('document');
    $this->assertDatabaseCount('student_applications', 0);
    $this->post(route('registration.store'), [...$data, 'document' => UploadedFile::fake()->image('certificate.png')])
        ->assertSessionHasNoErrors();

    expect(StudentApplication::sole()->entrance_exam_required)->toBe($examRequired);
    Storage::disk('student_documents')->assertExists(StudentApplication::sole()->document_path);
})->with([
    ['current_student', false], ['transferred_student', true], ['traveler', true],
]);

test('registration validation repopulates the selected system and grade', function () {
    $grade = EducationalGrade::factory()->american(6)->create();
    EducationalGrade::factory()->create();
    $this->from(route('registration.create'))->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'student_name' => '',
    ]))->assertSessionHasErrors('student_name');

    $response = $this->get(route('registration.create'));
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);
    expect($xpath->evaluate('string(//select[@id="education_system_id"]/option[@selected]/@value)'))->toBe((string) $grade->educationalStage->education_system_id);
    expect($xpath->evaluate('string(//select[@id="educational_grade_id"]/option[@selected]/@value)'))->toBe((string) $grade->id);
});

test('American registration rejects invalid grade identifiers', function (mixed $id) {
    $system = EducationSystem::factory()->american()->create();

    $this->post(route('registration.store'), registrationData([
        'education_system_id' => $system->id, 'educational_grade_id' => $id,
    ]))->assertSessionHasErrors('educational_grade_id');

    $this->assertDatabaseCount('student_applications', 0);
})->with(['missing' => null, 'unknown' => 999999, 'array' => [[1]], 'text' => 'invalid']);
