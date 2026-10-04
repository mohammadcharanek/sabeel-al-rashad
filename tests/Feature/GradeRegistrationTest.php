<?php

use App\Filament\Resources\StudentApplications\Pages\ListStudentApplications;
use App\Filament\Resources\StudentApplications\Pages\ViewStudentApplication;
use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\StudentApplication;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

dataset('kindergarten admissions', [
    'KG1 new' => ['kg1', 'new_student', false, true],
    'KG2 new' => ['kg2', 'new_student', false, true],
    'KG3 new' => ['kg3', 'new_student', false, true],
    'KG1 transfer' => ['kg1', 'transferred_student', true, true],
    'KG2 transfer' => ['kg2', 'transferred_student', true, true],
    'KG3 transfer' => ['kg3', 'transferred_student', true, true],
    'KG1 traveler' => ['kg1', 'traveler', false, true],
    'KG2 traveler' => ['kg2', 'traveler', true, true],
    'KG3 traveler' => ['kg3', 'traveler', true, true],
    'KG1 current' => ['kg1', 'current_student', true, false],
    'KG2 current' => ['kg2', 'current_student', true, false],
    'KG3 current' => ['kg3', 'current_student', true, false],
]);

test('grade admission requirements are authoritative and stage is derived', function (string $code, string $type, bool $document, bool $interview) {
    Storage::fake('student_documents');
    $stage = EducationalStage::factory()->create(['category' => 'kindergarten']);
    $grade = EducationalGrade::factory()->for($stage, 'educationalStage')->create(['code' => $code]);
    $data = registrationData([
        'educational_grade_id' => $grade->id,
        'educational_stage_id' => 999999,
        'registration_type' => $type,
        'document_required' => ! $document,
        'interview_required' => ! $interview,
        'entrance_exam_required' => true,
        'status' => 'accepted',
        'admin_note' => 'forged',
    ]);
    if ($document) {
        $this->post(route('registration.store'), $data)->assertSessionHasErrors('document');
        $this->assertDatabaseCount('student_applications', 0);
        $data['document'] = UploadedFile::fake()->image('certificate.png');
    }

    $this->post(route('registration.store'), $data)->assertSessionHasNoErrors()->assertRedirectToRoute('registration.confirmation');
    $application = StudentApplication::sole();
    expect($application->educational_grade_id)->toBe($grade->id);
    expect($application->educational_stage_id)->toBe($stage->id);
    expect($application->registration_type)->toBe($type);
    expect($application->document_required)->toBe($document);
    expect($application->interview_required)->toBe($interview);
    expect($application->entrance_exam_required)->toBeFalse();
    expect($application->status)->toBe('pending');
    expect($application->admin_note)->toBeNull();
    if ($document) {
        Storage::disk('student_documents')->assertExists($application->document_path);
    } else {
        expect($application->document_path)->toBeNull();
        Storage::disk('student_documents')->assertDirectoryEmpty('/');
    }

    $this->get(route('registration.create'))->assertViewHas('requirements', fn (array $requirements): bool => $requirements[$grade->id][$type]['document_required'] === $document
        && $requirements[$grade->id][$type]['interview_required'] === $interview
        && $requirements[$grade->id][$type]['entrance_exam_required'] === false);
})->with('kindergarten admissions');

test('new students outside kindergarten receive a clear refusal without records or files', function (string $category) {
    Storage::fake('student_documents');
    $grade = EducationalGrade::factory()->for(EducationalStage::factory()->create(['category' => $category]), 'educationalStage')->create();
    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'new_student',
        'document' => UploadedFile::fake()->image('certificate.png'),
    ]))->assertSessionHasErrors(['registration_type' => 'تسجيل الطالب الجديد متاح حالياً لصفوف الروضات فقط. يرجى التواصل مع إدارة المدرسة.']);
    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with(['basic', 'primary', 'intermediate', 'secondary']);

test('registration groups grades by stage sort order then orders grades within each stage', function () {
    $laterStage = EducationalStage::factory()->create(['sort_order' => 20]);
    $firstStage = EducationalStage::factory()->create(['sort_order' => 10]);
    $tiedStage = EducationalStage::factory()->create(['sort_order' => 10]);
    $last = EducationalGrade::factory()->for($laterStage, 'educationalStage')->create(['sort_order' => 0]);
    $second = EducationalGrade::factory()->for($firstStage, 'educationalStage')->create(['sort_order' => 20]);
    $first = EducationalGrade::factory()->for($firstStage, 'educationalStage')->create(['sort_order' => 10]);
    $third = EducationalGrade::factory()->for($firstStage, 'educationalStage')->create(['sort_order' => 20]);
    $fourth = EducationalGrade::factory()->for($tiedStage, 'educationalStage')->create(['sort_order' => 0]);

    $this->get(route('registration.create'))
        ->assertViewHas('grades', fn ($grades): bool => $grades->modelKeys() === [$first->id, $second->id, $third->id, $fourth->id, $last->id]);
});

test('only active grades with a valid active classified stage are available', function (string $invalid) {
    $available = EducationalGrade::factory()->create(['name_ar' => 'صف متاح']);
    $hidden = EducationalGrade::factory()->create(['name_ar' => 'صف محجوب']);
    match ($invalid) {
        'inactive grade' => $hidden->update(['is_active' => false]),
        'inactive stage' => $hidden->educationalStage->update(['is_active' => false]),
        'unclassified stage' => DB::table('educational_stages')->where('id', $hidden->educational_stage_id)->update(['category' => null]),
        'invalid category' => DB::table('educational_stages')->where('id', $hidden->educational_stage_id)->update(['category' => 'unknown']),
        'mismatched code' => DB::table('educational_grades')->where('id', $hidden->id)->update(['code' => 'kg1']),
    };
    $this->get(route('registration.create'))->assertSee($available->name_ar)->assertDontSee($hidden->name_ar)
        ->assertSee('المتطلبات الخاصة بالتسجيل')->assertSee('name="educational_grade_id"', false)
        ->assertDontSee('name="educational_stage_id"', false)->assertSee('aria-live="polite"', false)
        ->assertSee('<noscript>', false)->assertSee('dir="rtl"', false);
    $this->post(route('registration.store'), registrationData(['educational_grade_id' => $hidden->id]))
        ->assertSessionHasErrors(['educational_grade_id' => 'يرجى اختيار صف متاح ضمن مرحلة مصنفة.']);
    $this->assertDatabaseCount('student_applications', 0);
})->with(['inactive grade', 'inactive stage', 'unclassified stage', 'invalid category', 'mismatched code']);

test('optional uploads retain security validation', function () {
    Storage::fake('student_documents');
    $grade = EducationalGrade::factory()->for(EducationalStage::factory()->create(['category' => 'kindergarten']), 'educationalStage')->create(['code' => 'kg1']);
    $this->post(route('registration.store'), registrationData([
        'educational_grade_id' => $grade->id, 'registration_type' => 'new_student',
        'document' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
    ]))->assertSessionHasErrors('document');
    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
});

test('grade interview and exam filters preserve applications and historical displays', function () {
    $grade = EducationalGrade::factory()->for(EducationalStage::factory()->create(['category' => 'kindergarten']), 'educationalStage')->create(['code' => 'kg1']);
    $interview = StudentApplication::factory()->create(['educational_grade_id' => $grade->id, 'educational_stage_id' => $grade->educational_stage_id, 'registration_type' => 'new_student']);
    $schoolGrade = EducationalGrade::factory()->create();
    $exam = StudentApplication::factory()->create(['educational_grade_id' => $schoolGrade->id, 'educational_stage_id' => $schoolGrade->educational_stage_id, 'registration_type' => 'traveler']);
    $historical = StudentApplication::factory()->create(['educational_stage_id' => $grade->educational_stage_id, 'registration_type' => 'traveler']);
    $original = StudentApplication::orderBy('id')->get()->map->getRawOriginal()->all();
    $this->actingAs(User::factory()->admin()->create());
    Livewire::test(ListStudentApplications::class)->filterTable('interview_required', true)
        ->assertCanSeeTableRecords([$interview])->assertCanNotSeeTableRecords([$exam, $historical])
        ->assertTableColumnStateSet('interview_requirement', 'مطلوب', $interview);
    Livewire::test(ListStudentApplications::class)->filterTable('interview_required', false)
        ->assertCanSeeTableRecords([$exam, $historical])->assertCanNotSeeTableRecords([$interview]);
    Livewire::test(ListStudentApplications::class)->filterTable('entrance_exam_required', true)
        ->assertCanSeeTableRecords([$exam, $historical])->assertCanNotSeeTableRecords([$interview]);
    Livewire::test(ListStudentApplications::class)->filterTable('educational_grade_id', $grade->id)
        ->filterTable('registration_type', 'new_student')->filterTable('educational_stage_id', $grade->educational_stage_id)
        ->filterTable('status', 'pending')->assertCanSeeTableRecords([$interview])->assertCanNotSeeTableRecords([$exam, $historical]);
    Livewire::test(ViewStudentApplication::class, ['record' => $historical->id])
        ->assertSee('الصف غير محدد في الطلب القديم')->assertSee('يخضع الطالب لامتحان دخول.');
    Livewire::test(ViewStudentApplication::class, ['record' => $interview->id])->assertSee('يلزم إجراء مقابلة.')->assertSee('لا يلزم إرفاق إفادة.');
    expect(StudentApplication::orderBy('id')->get()->map->getRawOriginal()->all())->toBe($original);
});
