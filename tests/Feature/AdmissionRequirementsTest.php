<?php

use App\Filament\Resources\StudentApplications\Pages\ListStudentApplications;
use App\Filament\Resources\StudentApplications\Pages\ViewStudentApplication;
use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\StudentApplication;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('Grade 10 transfers accept a statement a certificate or both and still require an exam', function (bool $american, string $documentType) {
    Storage::fake('student_documents');
    $grade = $american ? EducationalGrade::factory()->american(10)->create()
        : EducationalGrade::factory()->for(EducationalStage::factory()->state(['category' => 'secondary']), 'educationalStage')->create(['code' => 'grade_10']);

    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'transferred_student',
        'document_type' => $documentType, 'document' => UploadedFile::fake()->image('school-documents.png'),
        'entrance_exam_required' => false, 'interview_required' => true,
    ]))->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->document_type)->toBe($documentType);
    expect($application->document_required)->toBeTrue();
    expect($application->entrance_exam_required)->toBeTrue();
    expect($application->interview_required)->toBeFalse();
    Storage::disk('student_documents')->assertExists($application->document_path);
})->with(['Lebanese' => false, 'American' => true])->with(['school_statement', 'school_certificate', 'statement_and_certificate']);

test('Grade 10 transfer document declarations cannot replace an actual upload', function (bool $american) {
    Storage::fake('student_documents');
    $grade = $american ? EducationalGrade::factory()->american(10)->create()
        : EducationalGrade::factory()->create(['code' => 'grade_10']);

    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'transferred_student',
        'document_type' => 'statement_and_certificate', 'document_path' => 'documents/forged.pdf',
    ]))->assertSessionHasErrors(['document' => 'يرجى إرفاق المستند المطلوب وفق حالة الطالب والصف المختار.']);

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with([false, true]);

test('ordinary transfers cannot substitute incompatible documents for the previous school statement', function (bool $american, string $documentType) {
    Storage::fake('student_documents');
    $grade = $american ? EducationalGrade::factory()->american(6)->create()
        : EducationalGrade::factory()->create(['code' => 'grade_5']);

    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'transferred_student',
        'document_type' => $documentType, 'document' => UploadedFile::fake()->image('certificate.png'),
    ]))->assertSessionHasErrors(['document_type' => 'نوع المستند لا يطابق متطلبات حالة الطالب والصف المختار.']);

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with([false, true])->with(['school_certificate', 'statement_and_certificate', 'foreign_statement']);

test('Grade 10 transfers reject an upload without an eligible document declaration', function (bool $american, ?string $documentType) {
    Storage::fake('student_documents');
    $grade = $american ? EducationalGrade::factory()->american(10)->create()
        : EducationalGrade::factory()->create(['code' => 'grade_10']);

    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'transferred_student',
        'document_type' => $documentType, 'document' => UploadedFile::fake()->image('document.png'),
        'document_required' => false,
    ]))->assertSessionHasErrors('document_type');

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with(['Lebanese' => false, 'American' => true])->with(['neither declared' => null, 'foreign substitute' => 'foreign_statement']);

test('transfer statements require an exam only after Kindergarten regardless of IDs or ordering', function (string $category, string $code, bool $american, bool $exam) {
    Storage::fake('student_documents');
    $stageFactory = $american ? EducationalStage::factory()->american($category) : EducationalStage::factory()->state(['category' => $category]);
    $stage = $stageFactory->create(['id' => $exam ? 2 : 900, 'sort_order' => $exam ? 0 : 900]);
    $grade = EducationalGrade::factory()->for($stage, 'educationalStage')->create(['code' => $code, 'id' => $exam ? 3 : 901]);

    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'transferred_student',
        'document_type' => 'school_statement', 'document' => UploadedFile::fake()->image('statement.png'),
        'foreign_document_attestation_confirmed' => '1', 'requires_exam' => ! $exam,
        'entrance_exam_required' => ! $exam, 'requires_interview' => true,
    ]))->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->educational_grade_id)->toBe($grade->id);
    expect($application->document_type)->toBe('school_statement');
    expect($application->document_required)->toBeTrue();
    expect($application->foreign_document_attestation_confirmed)->toBeNull();
    expect($application->entrance_exam_required)->toBe($exam);
    expect($application->interview_required)->toBeFalse();
    Storage::disk('student_documents')->assertExists($application->document_path);
})->with([
    'Kindergarten with high IDs' => ['kindergarten', 'kg1', false, false],
    'Lebanese Grade 6 with low IDs' => ['basic', 'grade_6', false, true],
    'American Grade 1' => ['elementary', 'american_grade_1', true, true],
    'American Grade 5' => ['elementary', 'american_grade_5', true, true],
    'American Grade 6' => ['middle_school', 'american_grade_6', true, true],
    'American Grade 8' => ['middle_school', 'american_grade_8', true, true],
    'American Grade 9' => ['high_school', 'american_grade_9', true, true],
    'American Grade 12' => ['high_school', 'american_grade_12', true, true],
]);

dataset('abroad grades', [
    'Kindergarten' => ['kg1', false, false],
    'Lebanese Grade 7' => ['grade_7', false, true],
    'American Grade 11' => ['american_grade_11', true, true],
    'American Grade 10' => ['american_grade_10', true, true],
]);

test('abroad applicants require a foreign statement attestation and the appropriate exam', function (string $code, bool $american, bool $exam) {
    Storage::fake('student_documents');
    $grade = $american ? EducationalGrade::factory()->american((int) substr($code, 15))->create()
        : EducationalGrade::factory()->for(EducationalStage::factory()->state(['category' => $code === 'kg1' ? 'kindergarten' : 'basic']), 'educationalStage')->create(['code' => $code]);
    $data = registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'traveler',
        'document_type' => 'foreign_statement', 'foreign_document_attestation_confirmed' => '1',
        'document' => UploadedFile::fake()->image('attested-statement.png'),
        'entrance_exam_required' => ! $exam,
    ]);

    $this->post(route('registration.store'), $data)->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->document_type)->toBe('foreign_statement');
    expect($application->foreign_document_attestation_confirmed)->toBeTrue();
    expect($application->entrance_exam_required)->toBe($exam);
    expect($application->interview_required)->toBeFalse();
    Storage::disk('student_documents')->assertExists($application->document_path);
})->with('abroad grades');

test('abroad submissions reject missing foreign documents missing attestation and local substitutes', function (array $overrides, string $field, string $code) {
    Storage::fake('student_documents');
    $grade = $code === 'american_grade_10' ? EducationalGrade::factory()->american(10)->create()
        : EducationalGrade::factory()->for(EducationalStage::factory()->state(['category' => 'kindergarten']), 'educationalStage')->create(['code' => 'kg1']);

    $this->post(route('registration.store'), registrationData(array_replace([
        'educational_grade_id' => $grade->id, 'registration_type' => 'traveler',
        'document_type' => 'foreign_statement', 'foreign_document_attestation_confirmed' => '1',
        'document' => UploadedFile::fake()->image('statement.png'),
    ], $overrides)))->assertSessionHasErrors($field);

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with([
    'no file' => [['document' => null], 'document'],
    'no confirmation' => [['foreign_document_attestation_confirmed' => null], 'foreign_document_attestation_confirmed'],
    'denied confirmation' => [['foreign_document_attestation_confirmed' => '0'], 'foreign_document_attestation_confirmed'],
    'malformed confirmation' => [['foreign_document_attestation_confirmed' => ['1']], 'foreign_document_attestation_confirmed'],
    'local statement' => [['document_type' => 'school_statement'], 'document_type'],
    'local certificate' => [['document_type' => 'school_certificate'], 'document_type'],
    'both local documents' => [['document_type' => 'statement_and_certificate'], 'document_type'],
    'no document type' => [['document_type' => null], 'document_type'],
])->with(['kg1', 'american_grade_10']);

test('document type is required and validated independently of a valid upload', function (mixed $type) {
    Storage::fake('student_documents');

    $this->post(route('registration.store'), registrationData([
        'registration_type' => 'transferred_student', 'document_type' => $type,
        'document' => UploadedFile::fake()->image('statement.png'),
    ]))->assertSessionHasErrors('document_type');

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with(['missing' => null, 'unknown' => 'other', 'malformed' => [['school_statement']]]);

test('new students at every level require only an interview and ignore forged admission metadata', function (string $category, string $code, bool $american) {
    Storage::fake('student_documents');
    $grade = $american ? EducationalGrade::factory()->american(6)->create()
        : EducationalGrade::factory()->for(EducationalStage::factory()->state(['category' => $category]), 'educationalStage')->create(['code' => $code]);

    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'new_student',
        'document_type' => 'foreign_statement', 'foreign_document_attestation_confirmed' => '1',
        'interview_required' => false, 'entrance_exam_required' => true,
        'requires_interview' => false, 'requires_exam' => true, 'document_required' => true,
    ]))->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->educational_grade_id)->toBe($grade->id);
    expect($application->interview_required)->toBeTrue();
    expect($application->entrance_exam_required)->toBeFalse();
    expect($application->document_required)->toBeFalse();
    expect($application->document_type)->toBeNull();
    expect($application->foreign_document_attestation_confirmed)->toBeNull();
    expect($application->document_path)->toBeNull();
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with([
    'Kindergarten' => ['kindergarten', 'kg1', false],
    'higher Lebanese grade' => ['intermediate', 'grade_7', false],
    'higher American grade' => ['middle_school', 'american_grade_6', true],
]);

test('admin displays declared document details without claiming that attestation was verified', function (?bool $attested, string $label) {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $grade = EducationalGrade::factory()->american(11)->create();
    $application = StudentApplication::factory()->create([
        'educational_grade_id' => $grade->id, 'educational_stage_id' => $grade->educational_stage_id,
        'registration_type' => 'traveler', 'document_type' => $attested === null ? null : 'foreign_statement',
        'foreign_document_attestation_confirmed' => $attested,
    ]);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ViewStudentApplication::class, ['record' => $application->id])
        ->assertSee($label)->assertSee('يخضع الطالب لامتحان دخول.')->assertSee('Grade 11');
})->with([
    'confirmed' => [true, 'أكد مقدم الطلب التصديق من لبنان — يلزم مراجعة المستند'],
    'not confirmed' => [false, 'لم يؤكد مقدم الطلب التصديق من لبنان'],
    'historical unknown' => [null, 'غير مسجل في الطلب القديم'],
]);

test('admin interview filters include higher grade new students in both systems without modifying records', function (bool $american) {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $grade = $american ? EducationalGrade::factory()->american(6)->create() : EducationalGrade::factory()->create(['code' => 'grade_7']);
    $new = StudentApplication::factory()->create([
        'educational_grade_id' => $grade->id, 'educational_stage_id' => $grade->educational_stage_id,
        'registration_type' => 'new_student',
    ]);
    $transfer = StudentApplication::factory()->create([
        'educational_grade_id' => $grade->id, 'educational_stage_id' => $grade->educational_stage_id,
        'registration_type' => 'transferred_student',
    ]);
    $original = $new->refresh()->getRawOriginal();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListStudentApplications::class)->filterTable('interview_required', true)
        ->assertCanSeeTableRecords([$new])->assertCanNotSeeTableRecords([$transfer])
        ->assertTableColumnStateSet('interview_requirement', 'مطلوب', $new)
        ->assertTableColumnStateSet('exam_requirement', 'غير مطلوب', $new);
    Livewire::test(ViewStudentApplication::class, ['record' => $new->id])->assertSee('يلزم إجراء مقابلة.');
    expect($new->fresh()->getRawOriginal())->toBe($original);
})->with([false, true]);

test('validation restores document type and attestation alongside academic selections', function () {
    $grade = EducationalGrade::factory()->american(11)->create();
    $this->from(route('registration.create'))->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'traveler',
        'document_type' => 'foreign_statement', 'foreign_document_attestation_confirmed' => '1',
    ]))->assertSessionHasErrors('document');

    $response = $this->get(route('registration.create'))->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);
    expect($xpath->evaluate('string(//select[@id="document_type"]/option[@selected]/@value)'))->toBe('foreign_statement');
    expect($xpath->evaluate('count(//input[@id="foreign_document_attestation_confirmed"][@checked][@required])'))->toBe(1.0);
});
