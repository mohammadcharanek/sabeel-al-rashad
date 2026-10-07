<?php

use App\Filament\Resources\StudentApplications\Pages\EditStudentApplication;
use App\Filament\Resources\StudentApplications\Pages\ListStudentApplications;
use App\Filament\Resources\StudentApplications\Pages\ViewStudentApplication;
use App\Filament\Resources\StudentApplications\StudentApplicationResource;
use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\StudentApplication;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'production']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('American application displays and requirement filters remain readable when the system is inactive', function () {
    $grade = EducationalGrade::factory()->american(9)->create();
    $current = StudentApplication::factory()->create([
        'educational_grade_id' => $grade->id, 'educational_stage_id' => $grade->educational_stage_id,
    ]);
    $transfer = StudentApplication::factory()->create([
        'educational_grade_id' => $grade->id, 'educational_stage_id' => $grade->educational_stage_id,
        'registration_type' => 'transferred_student',
    ]);
    $grade->educationalStage->educationSystem->update(['is_active' => false]);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListStudentApplications::class)->filterTable('entrance_exam_required', true)
        ->assertCanSeeTableRecords([$transfer])->assertCanNotSeeTableRecords([$current])
        ->assertTableColumnStateSet('educationalStage.educationSystem.name', 'المنهج الأميركي', $transfer);
    Livewire::test(ListStudentApplications::class)->filterTable('entrance_exam_required', false)
        ->assertCanSeeTableRecords([$current])->assertCanNotSeeTableRecords([$transfer]);
    Livewire::test(ListStudentApplications::class)->filterTable('interview_required', false)
        ->assertCanSeeTableRecords([$current, $transfer]);
    Livewire::test(ListStudentApplications::class)->filterTable('interview_required', true)
        ->assertCanNotSeeTableRecords([$current, $transfer]);
    Livewire::test(ViewStudentApplication::class, ['record' => $transfer->id])
        ->assertSee('المنهج الأميركي')->assertSee('Grade 9')->assertSee('يخضع الطالب لامتحان دخول.');
});

test('application list view and edit URLs enforce admin access', function (string $role) {
    $application = StudentApplication::factory()->create();

    if ($role !== 'guest') {
        $this->actingAs($role === 'admin' ? User::factory()->admin()->create() : User::factory()->create());
    }

    foreach (['index', 'view', 'edit'] as $page) {
        $response = $this->get(StudentApplicationResource::getUrl($page, $page === 'index' ? [] : ['record' => $application]));

        match ($role) {
            'guest' => $response->assertRedirect(route('filament.admin.auth.login')),
            'user' => $response->assertForbidden(),
            'admin' => $response->assertOk(),
        };
    }
})->with(['guest', 'user', 'admin']);

test('application policies permit only admin reading updating and downloading and disallow removal', function (bool $isAdmin) {
    $user = User::factory()->create(['is_admin' => $isAdmin]);
    $application = StudentApplication::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', StudentApplication::class))->toBe($isAdmin);
    foreach (['view', 'update', 'downloadDocument'] as $ability) {
        expect(Gate::forUser($user)->allows($ability, $application))->toBe($isAdmin);
    }
    foreach (['create', 'deleteAny'] as $ability) {
        expect(Gate::forUser($user)->allows($ability, StudentApplication::class))->toBeFalse();
    }
    expect(Gate::forUser($user)->allows('delete', $application))->toBeFalse();
})->with([true, false]);

test('administrators see full details and escaped notes with an authorized document link', function () {
    $application = StudentApplication::factory()->create([
        'guardian_email' => 'guardian@example.test',
        'notes' => '<script>alert("guardian")</script>',
        'admin_note' => '<script>alert("staff")</script>',
        'document_path' => 'documents/'.str_repeat('a', 40).'.pdf',
        'document_original_name' => 'certificate.pdf',
    ]);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ViewStudentApplication::class, ['record' => $application->id])
        ->assertSee($application->reference_number)
        ->assertSee($application->student_name)
        ->assertSee($application->guardian_email)
        ->assertSee($application->date_of_birth->format('Y-m-d'))
        ->assertSee($application->submitted_at->format('Y-m-d H:i'))
        ->assertSee(route('student-applications.document', $application), false)
        ->assertDontSee('<script>alert(', false);
});

test('administrators can transition between every allowed status and edit internal notes only', function () {
    $application = StudentApplication::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $component = Livewire::test(EditStudentApplication::class, ['record' => $application->id]);

    foreach (['under_review', 'accepted', 'rejected', 'pending'] as $status) {
        $component->fillForm(['status' => $status, 'admin_note' => 'مراجعة داخلية'])
            ->set('data.student_name', 'تعديل غير مسموح')
            ->set('data.document_path', '../secret.pdf')
            ->set('data.registration_type', 'traveler')
            ->set('data.entrance_exam_required', false)
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('student_applications', [
            'id' => $application->id,
            'status' => $status,
            'admin_note' => 'مراجعة داخلية',
            'student_name' => $application->student_name,
            'document_path' => null,
            'registration_type' => $application->registration_type,
        ]);
    }
});

test('invalid status and oversized internal notes cannot be saved', function (array $data, string $field) {
    $application = StudentApplication::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditStudentApplication::class, ['record' => $application->id])
        ->fillForm($data)->call('save')->assertHasFormErrors([$field]);

    expect($application->fresh()->status)->toBe('pending');
    expect($application->fresh()->admin_note)->toBeNull();
})->with([
    'invalid status' => [['status' => 'archived'], 'status'],
    'missing status' => [['status' => null], 'status'],
    'long note' => [['admin_note' => str_repeat('a', 5001)], 'admin_note'],
]);

test('non admins cannot directly mount a management component', function () {
    $application = StudentApplication::factory()->create();
    $this->actingAs(User::factory()->create());

    Livewire::test(EditStudentApplication::class, ['record' => $application->id])->assertForbidden();
    expect($application->fresh()->status)->toBe('pending');
});

test('revoking admin access blocks subsequent application edits', function () {
    $user = User::factory()->admin()->create();
    $application = StudentApplication::factory()->create();
    $this->actingAs($user);
    $component = Livewire::test(EditStudentApplication::class, ['record' => $application->id])
        ->fillForm(['status' => 'accepted']);
    $user->forceFill(['is_admin' => false])->save();

    $component->call('save')->assertForbidden();
    expect($application->fresh()->status)->toBe('pending');
});

test('staff can search each requested application field', function (string $field) {
    $target = StudentApplication::factory()->create([
        'student_name' => 'خالد سمير محمود',
        'guardian_name' => 'سمير محمود',
        'guardian_phone' => '71112233',
    ]);
    $other = StudentApplication::factory()->create([
        'guardian_name' => 'حسن نادر',
        'guardian_phone' => '03998877',
    ]);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListStudentApplications::class)
        ->searchTable($target->{$field})
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
})->with(['student_name', 'guardian_name', 'guardian_phone', 'reference_number']);

test('staff can combine status and educational stage filters', function () {
    $stage = EducationalStage::factory()->create();
    $target = StudentApplication::factory()->for($stage, 'educationalStage')->create(['status' => 'under_review']);
    $wrongStatus = StudentApplication::factory()->for($stage, 'educationalStage')->create();
    $wrongStage = StudentApplication::factory()->create(['status' => 'under_review']);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListStudentApplications::class)
        ->filterTable('status', 'under_review')
        ->filterTable('educational_stage_id', $stage->id)
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$wrongStatus, $wrongStage]);
});

test('staff can display and filter every registration type', function (string $type, string $label) {
    $target = StudentApplication::factory()->create(['registration_type' => $type]);
    $other = StudentApplication::factory()->create(['registration_type' => $type === 'traveler' ? 'current_student' : 'traveler']);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListStudentApplications::class)->filterTable('registration_type', $type)
        ->assertCanSeeTableRecords([$target])->assertCanNotSeeTableRecords([$other])->assertSee($label);
    Livewire::test(ViewStudentApplication::class, ['record' => $target->id])->assertSee($label)
        ->assertDontSee(route('student-applications.document', $target), false);
})->with([
    ['current_student', 'طالب حالي'], ['transferred_student', 'طالب منتقل من مدرسة أخرى'], ['traveler', 'طالب مسافر'],
]);

test('exam filters match derived requirements and keep unclassified historical applications distinct', function () {
    $primary = EducationalStage::factory()->create(['category' => 'primary']);
    $kindergarten = EducationalStage::factory()->create(['category' => 'kindergarten']);
    $unknown = EducationalStage::factory()->create(['category' => null]);
    $required = StudentApplication::factory()->for($primary, 'educationalStage')->create(['registration_type' => 'transferred_student']);
    $traveler = StudentApplication::factory()->for($primary, 'educationalStage')->create(['registration_type' => 'traveler']);
    $exempt = StudentApplication::factory()->for($kindergarten, 'educationalStage')->create(['registration_type' => 'transferred_student']);
    $current = StudentApplication::factory()->for($primary, 'educationalStage')->create();
    $historical = StudentApplication::factory()->for($unknown, 'educationalStage')->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListStudentApplications::class)->filterTable('entrance_exam_required', true)
        ->assertCanSeeTableRecords([$required, $traveler])->assertCanNotSeeTableRecords([$current, $exempt, $historical]);
    Livewire::test(ListStudentApplications::class)->filterTable('entrance_exam_required', false)
        ->assertCanSeeTableRecords([$current, $exempt])->assertCanNotSeeTableRecords([$required, $traveler, $historical]);
    Livewire::test(ListStudentApplications::class)
        ->filterTable('registration_type', 'transferred_student')
        ->filterTable('educational_stage_id', $primary->id)
        ->filterTable('status', 'pending')
        ->filterTable('entrance_exam_required', true)
        ->assertCanSeeTableRecords([$required])->assertCanNotSeeTableRecords([$current, $traveler, $exempt, $historical]);

    Livewire::test(ViewStudentApplication::class, ['record' => $historical->id])
        ->assertSee('يلزم تصنيف المرحلة لتحديد المتطلبات')->assertSee('طالب حالي');
    expect($historical->entrance_exam_required)->toBeNull();
    Livewire::test(ViewStudentApplication::class, ['record' => $exempt->id])
        ->assertSee('لا يوجد امتحان دخول لهذه المرحلة.')->assertSee('المستند المطلوب: إفادة من المدرسة السابقة.');
    Livewire::test(ViewStudentApplication::class, ['record' => $required->id])
        ->assertSee('يخضع الطالب لامتحان دخول.')->assertSee('المستند المطلوب: إفادة من المدرسة السابقة.');
    Livewire::test(ViewStudentApplication::class, ['record' => $current->id])
        ->assertSee('لا يخضع الطالب الحالي لامتحان دخول.');
});

test('exam filters and table cells follow all registration cases without changing stored applications', function (string $category, bool $transferredExam) {
    $stage = EducationalStage::factory()->create(['category' => $category]);
    $current = StudentApplication::factory()->for($stage, 'educationalStage')->create();
    $transferred = StudentApplication::factory()->for($stage, 'educationalStage')->create(['registration_type' => 'transferred_student']);
    $traveler = StudentApplication::factory()->for($stage, 'educationalStage')->create(['registration_type' => 'traveler']);
    $original = StudentApplication::orderBy('id')->get()->map->getRawOriginal()->all();
    $this->actingAs(User::factory()->admin()->create());
    $examRequired = $transferredExam ? [$transferred, $traveler] : [];
    $examExempt = $transferredExam ? [$current] : [$current, $transferred, $traveler];

    Livewire::test(ListStudentApplications::class)->filterTable('entrance_exam_required', true)
        ->assertCanSeeTableRecords($examRequired)->assertCanNotSeeTableRecords($examExempt);
    Livewire::test(ListStudentApplications::class)
        ->assertTableColumnStateSet('exam_requirement', $transferredExam ? 'مطلوب' : 'غير مطلوب', $traveler)
        ->assertTableColumnStateSet('document_requirement', 'مطلوب', $traveler);
    Livewire::test(ListStudentApplications::class)->filterTable('entrance_exam_required', false)
        ->assertCanSeeTableRecords($examExempt)->assertCanNotSeeTableRecords($examRequired)
        ->assertTableColumnStateSet('exam_requirement', 'غير مطلوب', $current)
        ->assertTableColumnStateSet('document_requirement', 'مطلوب', $current);

    expect(StudentApplication::orderBy('id')->get()->map->getRawOriginal()->all())->toBe($original);
})->with([
    'kindergarten' => ['kindergarten', false],
    'primary' => ['primary', true],
    'intermediate' => ['intermediate', true],
    'secondary' => ['secondary', true],
]);
