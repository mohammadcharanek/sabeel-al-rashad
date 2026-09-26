<?php

use App\Filament\Resources\StudentApplications\Pages\EditStudentApplication;
use App\Filament\Resources\StudentApplications\Pages\ListStudentApplications;
use App\Filament\Resources\StudentApplications\Pages\ViewStudentApplication;
use App\Filament\Resources\StudentApplications\StudentApplicationResource;
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
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('student_applications', [
            'id' => $application->id,
            'status' => $status,
            'admin_note' => 'مراجعة داخلية',
            'student_name' => $application->student_name,
            'document_path' => null,
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
