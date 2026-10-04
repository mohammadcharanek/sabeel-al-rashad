<?php

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\StudentApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('current students and transferred kindergarten require a document but no exam', function (string $category, string $type) {
    Storage::fake('student_documents');
    $stage = EducationalStage::factory()->create(['category' => $category]);

    $this->post(route('registration.store'), registrationData([
        'educational_stage_id' => $stage->id,
        'registration_type' => $type,
        'document' => UploadedFile::fake()->image('certificate.png'),
        'entrance_exam_required' => true,
        'document_required' => true,
        'document_original_name' => 'forged.pdf',
    ]))->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->registration_type)->toBe($type);
    expect($application->document_required)->toBeTrue();
    expect($application->entrance_exam_required)->toBeFalse();
    Storage::disk('student_documents')->assertExists($application->document_path);
    expect($application->document_original_name)->toBe('certificate.png');
})->with([
    'current kindergarten' => ['kindergarten', 'current_student'],
    'transferred kindergarten' => ['kindergarten', 'transferred_student'],
    'current primary' => ['primary', 'current_student'],
    'current basic' => ['basic', 'current_student'],
    'current intermediate' => ['intermediate', 'current_student'],
    'current secondary' => ['secondary', 'current_student'],
]);

test('travelers and transferred school students require documents and exams despite forged exemption fields', function (string $category, string $type) {
    Storage::fake('student_documents');
    $stage = EducationalStage::factory()->create(['category' => $category]);

    $this->post(route('registration.store'), registrationData([
        'educational_stage_id' => $stage->id,
        'registration_type' => $type,
        'document' => UploadedFile::fake()->image('certificate.png'),
        'entrance_exam_required' => false,
        'document_required' => false,
    ]))->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->registration_type)->toBe($type);
    expect($application->document_required)->toBeTrue();
    expect($application->entrance_exam_required)->toBeTrue();
    Storage::disk('student_documents')->assertExists($application->document_path);
})->with([
    'transferred primary' => ['primary', 'transferred_student'],
    'transferred basic' => ['basic', 'transferred_student'],
    'transferred intermediate' => ['intermediate', 'transferred_student'],
    'transferred secondary' => ['secondary', 'transferred_student'],
    'traveler primary' => ['primary', 'traveler'],
    'traveler basic' => ['basic', 'traveler'],
    'traveler intermediate' => ['intermediate', 'traveler'],
    'traveler secondary' => ['secondary', 'traveler'],
]);

test('forged submissions cannot bypass required documents without JavaScript', function (string $category, string $type) {
    Storage::fake('student_documents');
    $stage = EducationalStage::factory()->create(['category' => $category]);

    $this->post(route('registration.store'), registrationData([
        'educational_stage_id' => $stage->id,
        'registration_type' => $type,
        'entrance_exam_required' => false,
        'document_required' => false,
        'document_path' => 'documents/forged.pdf',
        'document_original_name' => 'forged.pdf',
    ]))->assertSessionHasErrors(['document' => 'يرجى إرفاق المستند المطلوب للتسجيل: إفادة أو شهادة نجاح.']);

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with(['kindergarten', 'primary', 'intermediate', 'secondary'])->with(['current_student', 'transferred_student', 'traveler']);

test('registration rejects missing invalid and malformed registration types', function (mixed $type) {
    $this->post(route('registration.store'), registrationData(['registration_type' => $type]))
        ->assertSessionHasErrors('registration_type');

    $this->assertDatabaseCount('student_applications', 0);
})->with(['missing' => null, 'unknown' => 'unknown', 'array' => [['traveler']]]);

test('unclassified and unknown categories are hidden and rejected even for travelers', function (?string $category, string $type) {
    $stage = EducationalStage::factory()->create(['title' => 'مرحلة غير مصنفة']);
    $grade = EducationalGrade::factory()->for($stage, 'educationalStage')->create();
    DB::table('educational_stages')->where('id', $stage->id)->update(['category' => $category]);
    EducationalGrade::factory()->create();

    $this->get(route('registration.create'))->assertDontSee($stage->title);
    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => $type,
    ]))->assertSessionHasErrors('educational_grade_id');

    $this->assertDatabaseCount('student_applications', 0);
})->with(['unclassified' => null, 'unknown category' => 'unknown'])->with(['current_student', 'transferred_student', 'traveler']);

test('registration selection errors preserve accessible feedback without malformed input crashing the form', function () {
    EducationalGrade::factory()->create();
    $response = $this->from(route('registration.create'))->post(route('registration.store'), registrationData([
        'educational_grade_id' => ['1'], 'registration_type' => ['traveler'],
    ]))->assertSessionHasErrors(['educational_grade_id', 'registration_type']);

    $this->withCookie(config('session.cookie'), $response->getCookie(config('session.cookie'))->getValue())
        ->get(route('registration.create'))->assertOk()
        ->assertSee('registration-type-help registration_type-error', false)
        ->assertSee('aria-describedby="educational_grade_id-error"', false);
});

test('registration renders server generated requirements with live feedback and a no JavaScript explanation', function () {
    $primary = EducationalGrade::factory()->create();
    $kindergarten = EducationalGrade::factory()->for(EducationalStage::factory()->create(['category' => 'kindergarten']), 'educationalStage')->create(['code' => 'kg2']);

    $response = $this->get(route('registration.create'))
        ->assertSee('طالب منتقل من مدرسة أخرى إلى ثانوية سبيل الرشاد')
        ->assertSee('for="registration_type"', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('aria-atomic="true"', false)
        ->assertSee('document-help registration-requirements', false)
        ->assertSee('<noscript>', false)
        ->assertSee('sm:grid-cols-2', false)
        ->assertSee('dir="rtl"', false)
        ->assertViewHas('requirements', fn (array $requirements): bool => $requirements[$primary->id]['transferred_student']['document_required'] === true
            && $requirements[$primary->id]['traveler']['entrance_exam_required'] === true
            && $requirements[$primary->id]['current_student']['entrance_exam_required'] === false
            && $requirements[$kindergarten->id]['transferred_student']['document_required'] === true)
        ->assertSee('المستند المطلوب: إفادة من المدرسة أو الروضة السابقة.')
        ->assertSee('لا يخضع الطالب الحالي لامتحان دخول.')
        ->assertSee('المستند المطلوب: إفادة.')
        ->assertSee('يلزم إجراء مقابلة.');

    expect($response->getContent())->toContain('name="educational_grade_id"');
});

test('required document feedback restores the chosen registration situation and server summary', function () {
    $stage = EducationalStage::factory()->create(['category' => 'primary']);
    $response = $this->from(route('registration.create'))->post(route('registration.store'), registrationData([
        'educational_stage_id' => $stage->id, 'registration_type' => 'transferred_student',
    ]))->assertSessionHasErrors('document');

    $this->withCookie(config('session.cookie'), $response->getCookie(config('session.cookie'))->getValue())
        ->get(route('registration.create'))
        ->assertSee('value="transferred_student" selected', false)
        ->assertSee('المستند المطلوب: إفادة أو شهادة نجاح من المدرسة السابقة.')
        ->assertSee('يخضع الطالب لامتحان دخول.')
        ->assertSee('document-help registration-requirements document-error', false);
});

test('migrations and their rollbacks preserve historical data without assuming stage IDs', function () {
    DB::connection()->getPdo();
    $originalConnection = DB::getDefaultConnection();
    config(['database.connections.registration_migration_test' => [
        'driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true,
    ]]);
    DB::setDefaultConnection('registration_migration_test');

    try {
        (require database_path('migrations/2026_09_14_113313_create_educational_stages_table.php'))->up();
        (require database_path('migrations/2026_09_26_194452_create_student_applications_table.php'))->up();
        foreach ([1, 2, 3] as $id) {
            $stage = EducationalStage::factory()->make(['id' => $id, 'title' => 'عنوان لا يحدد التصنيف '.$id]);
            DB::table('educational_stages')->insert(collect($stage->getAttributes())->except('category')->all());
        }
        $legacy = StudentApplication::factory()->make([
            'educational_stage_id' => 1, 'status' => 'under_review', 'admin_note' => 'ملاحظة قديمة',
            'reference_number' => 'SAR-LEGACY', 'submitted_at' => '2026-09-01 10:00:00',
            'created_at' => '2026-09-01 10:00:00', 'updated_at' => '2026-09-02 12:00:00',
            'document_path' => 'documents/'.str_repeat('a', 40).'.pdf', 'document_original_name' => 'legacy.pdf',
        ]);
        DB::table('student_applications')->insert(collect($legacy->getAttributes())->except('registration_type')->all());
        $originalStages = DB::table('educational_stages')->orderBy('id')->get();
        $originalApplication = (array) DB::table('student_applications')->sole();

        (require database_path('migrations/2026_10_01_230556_add_category_to_educational_stages_table.php'))->up();
        (require database_path('migrations/2026_10_01_230558_add_registration_type_to_student_applications_table.php'))->up();

        expect(DB::table('educational_stages')->orderBy('id')->pluck('category')->all())->toBe([null, null, null]);
        foreach (DB::table('educational_stages')->orderBy('id')->get() as $index => $stage) {
            expect(collect((array) $stage)->except('category')->all())->toBe((array) $originalStages[$index]);
        }
        $application = StudentApplication::sole();
        expect(collect($application->getAttributes())->except('registration_type')->all())->toBe($originalApplication);
        expect($application->registration_type)->toBe('current_student');
        expect($application->educationalStage->category)->toBeNull();
        expect($application->entrance_exam_required)->toBeNull();

        (require database_path('migrations/2026_10_01_230558_add_registration_type_to_student_applications_table.php'))->down();
        (require database_path('migrations/2026_10_01_230556_add_category_to_educational_stages_table.php'))->down();

        expect((array) DB::table('student_applications')->sole())->toBe($originalApplication);
        expect(DB::table('educational_stages')->orderBy('id')->get()->toArray())->toEqual($originalStages->toArray());
    } finally {
        DB::setDefaultConnection($originalConnection);
        DB::purge('registration_migration_test');
    }
});
