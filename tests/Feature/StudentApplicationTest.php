<?php

use App\Models\EducationalStage;
use App\Models\SiteSetting;
use App\Models\StudentApplication;
use Illuminate\Database\QueryException;

test('guests see an Arabic registration form with only active stages and a homepage entry point', function () {
    $stage = EducationalStage::factory()->create(['title' => 'المرحلة الابتدائية']);
    $inactive = EducationalStage::factory()->inactive()->create(['title' => 'مرحلة غير متاحة']);

    $this->get(route('registration.create'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee($stage->title)
        ->assertDontSee($inactive->title)
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('name="_token"', false);
    $this->get(route('home'))->assertSee(route('registration.create'));
});

test('registration explains when no stage is available', function () {
    $this->get(route('registration.create'))
        ->assertSee('لا توجد مراحل متاحة للتسجيل حالياً.')
        ->assertDontSee('type="submit"', false);
});

test('registration keeps WhatsApp contact available without a floating form overlay', function () {
    SiteSetting::factory()->create(['whatsapp_enabled' => true, 'whatsapp_number' => '9613123456']);

    $this->get(route('registration.create'))
        ->assertSee('https://wa.me/9613123456', false)
        ->assertDontSee('fixed bottom-[calc(1rem+env(safe-area-inset-bottom))]', false);
    $this->get(route('home'))
        ->assertSee('fixed bottom-[calc(1rem+env(safe-area-inset-bottom))]', false);
});

test('valid submissions generate distinct references and keep personal details off the confirmation page', function () {
    $this->freezeTime();
    $data = registrationData([
        'guardian_email' => 'guardian@example.test',
        'notes' => 'ملاحظة خاصة لا تنشر',
        'status' => 'accepted',
        'admin_note' => 'forged',
        'reference_number' => 'forged',
        'document_path' => '../secret.pdf',
        'submitted_at' => '2000-01-01',
        'created_at' => '2000-01-01',
        'updated_at' => '2000-01-01',
    ]);

    $this->post(route('registration.store'), $data)
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->status)->toBe('pending');
    expect($application->reference_number)->toMatch('/^SAR-\d{4}(?:-[A-F0-9]{5}){4}$/');
    expect($application->submitted_at->toDateTimeString())->toBe(now()->toDateTimeString());
    expect($application->created_at->toDateTimeString())->toBe(now()->toDateTimeString());
    expect($application->updated_at->toDateTimeString())->toBe(now()->toDateTimeString());
    expect($application->date_of_birth->format('Y-m-d'))->toBe('2015-03-12');
    expect($application->guardian_phone)->toBe('03123456');
    expect($application->guardian_email)->toBe('guardian@example.test');
    expect($application->admin_note)->toBeNull();
    expect($application->document_path)->toBeNull();

    $this->get(route('registration.confirmation'))
        ->assertSee($application->reference_number)
        ->assertDontSee($data['student_name'])
        ->assertDontSee($data['guardian_email'])
        ->assertDontSee($data['notes'])
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Cache-Control', 'no-store, private');

    $this->post(route('registration.store'), $data)->assertSessionHasNoErrors();
    $this->assertDatabaseCount('student_applications', 2);
    expect(StudentApplication::pluck('reference_number')->unique())->toHaveCount(2);
});

test('confirmation cannot be retrieved with a reference or id from another session', function () {
    $application = StudentApplication::factory()->create();

    $this->get(route('registration.confirmation', ['reference' => $application->reference_number]))
        ->assertRedirectToRoute('registration.create');
    $this->get('/registration/'.$application->id)->assertNotFound();
});

test('all required fields have Arabic validation messages', function () {
    $this->from(route('registration.create'))->post(route('registration.store'), [])
        ->assertRedirectToRoute('registration.create')
        ->assertSessionHasErrors([
            'student_name' => 'حقل اسم الطالب الثلاثي مطلوب.',
            'date_of_birth' => 'حقل تاريخ الميلاد مطلوب.',
            'educational_stage_id' => 'حقل المرحلة التعليمية مطلوب.',
            'guardian_name' => 'حقل اسم ولي الأمر مطلوب.',
            'guardian_phone' => 'حقل هاتف ولي الأمر مطلوب.',
        ]);
    $this->assertDatabaseCount('student_applications', 0);
});

test('registration rejects invalid input without storing an application', function (string $field, mixed $value) {
    $this->travelTo(now()->setDate(2026, 9, 26)->startOfDay());
    $data = registrationData([$field => $value]);

    $this->post(route('registration.store'), $data)->assertSessionHasErrors($field);
    $this->assertDatabaseCount('student_applications', 0);
})->with([
    'two part name' => ['student_name', 'أحمد حسن'],
    'non Arabic name' => ['student_name', 'John James Smith'],
    'long student name' => ['student_name', str_repeat('أ', 151)],
    'name as array' => ['student_name', ['أحمد']],
    'future birthday' => ['date_of_birth', '2027-01-01'],
    'today birthday' => ['date_of_birth', '2026-09-26'],
    'invalid birthday' => ['date_of_birth', '2020-02-31'],
    'unparseable birthday' => ['date_of_birth', 'yesterday'],
    'nonexistent stage' => ['educational_stage_id', 999999],
    'invalid stage' => ['educational_stage_id', 'hello'],
    'long guardian name' => ['guardian_name', str_repeat('م', 151)],
    'phone letters' => ['guardian_phone', '03123456abc'],
    'short phone' => ['guardian_phone', '123'],
    'long phone' => ['guardian_phone', '+'.str_repeat('1', 16)],
    'zero phone' => ['guardian_phone', '00000000'],
    'embedded plus' => ['guardian_phone', '0312+3456'],
    'phone array' => ['guardian_phone', ['03123456']],
    'invalid email' => ['guardian_email', 'not-an-email'],
    'long email' => ['guardian_email', str_repeat('a', 250).'@example.test'],
    'long notes' => ['notes', str_repeat('أ', 3001)],
]);

test('inactive stages cannot be submitted directly', function () {
    $stage = EducationalStage::factory()->inactive()->create();

    $this->post(route('registration.store'), registrationData(['educational_stage_id' => $stage->id]))
        ->assertSessionHasErrors('educational_stage_id');
    $this->assertDatabaseCount('student_applications', 0);
});

test('phone formats are normalized and optional values can be omitted', function (string $input, string $expected) {
    $this->post(route('registration.store'), registrationData(['guardian_phone' => $input]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('student_applications', [
        'guardian_phone' => $expected,
        'guardian_email' => null,
        'notes' => null,
        'document_path' => null,
        'status' => 'pending',
    ]);
})->with([
    'local punctuation' => ['(03) 123-456', '03123456'],
    'international' => ['+961 (3) 123.456', '+9613123456'],
    'international prefix' => ['00961 3 123456', '+9613123456'],
    'Arabic digits' => ['٠٣ ١٢٣ ٤٥٦', '03123456'],
    'Persian digits' => ['۰۳ ۱۲۳ ۴۵۶', '03123456'],
]);

test('validation feedback associates errors with inputs and escapes previously entered notes', function () {
    $payload = '<script>alert("private")</script>';
    $response = $this->from(route('registration.create'))->post(route('registration.store'), registrationData([
        'student_name' => 'أحمد',
        'notes' => $payload,
    ]))->assertSessionHasErrors('student_name');

    $this->withCookie(config('session.cookie'), $response->getCookie(config('session.cookie'))->getValue())
        ->get(route('registration.create'))
        ->assertSee('role="alert"', false)
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('aria-describedby="student_name-error"', false)
        ->assertSee($payload)
        ->assertDontSee($payload, false);
});

test('submission rate limiting rejects excessive attempts without writing records', function () {
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->post(route('registration.store'), [])->assertSessionHasErrors();
    }

    $this->post(route('registration.store'), [])->assertTooManyRequests();
    $this->assertDatabaseCount('student_applications', 0);
});

test('malformed array input renders accessible errors instead of crashing the form', function () {
    $response = $this->from(route('registration.create'))->post(route('registration.store'), registrationData([
        'student_name' => ['أحمد'],
        'notes' => ['unexpected'],
    ]))->assertSessionHasErrors(['student_name', 'notes']);

    $this->withCookie(config('session.cookie'), $response->getCookie(config('session.cookie'))->getValue())
        ->get(route('registration.create'))
        ->assertOk()
        ->assertSee('aria-describedby="student_name-error"', false)
        ->assertSee('aria-describedby="notes-error"', false);
});

test('referenced educational stages cannot be deleted along with student applications', function () {
    $application = StudentApplication::factory()->create();

    expect(fn () => $application->educationalStage->delete())->toThrow(QueryException::class);
    $this->assertModelExists($application);
});
