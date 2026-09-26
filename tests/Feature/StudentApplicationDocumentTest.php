<?php

use App\Models\StudentApplication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('safe documents are stored privately with generated filenames', function (string $extension) {
    Storage::fake('student_documents');
    Storage::fake('public');
    $file = $extension === 'pdf'
        ? UploadedFile::fake()->createWithContent('certificate.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF")
        : UploadedFile::fake()->image('certificate.'.$extension);

    $this->post(route('registration.store'), registrationData(['document' => $file]))
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('registration.confirmation');

    $application = StudentApplication::sole();
    expect($application->document_path)->toMatch('/^documents\/[a-zA-Z0-9]{40}\.(pdf|jpg|jpeg|png)$/');
    expect($application->document_original_name)->toBe('certificate.'.$extension);
    Storage::disk('student_documents')->assertExists($application->document_path);
    Storage::disk('public')->assertDirectoryEmpty('/');
    $this->get('/storage/'.$application->document_path)->assertForbidden();
    $this->get('/storage/student-documents/'.$application->document_path)->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('student-applications.document', $application))
        ->assertDownload()
        ->assertHeader('Content-Type', 'application/octet-stream')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertStreamedContent($file->getContent());
})->with(['pdf', 'jpg', 'jpeg', 'png']);

test('dangerous and oversized documents are rejected without saving files or applications', function (string $kind) {
    Storage::fake('student_documents');
    $file = match ($kind) {
        'php' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
        'html' => UploadedFile::fake()->createWithContent('page.html', '<html><script>alert(1)</script></html>'),
        'svg' => UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'executable' => UploadedFile::fake()->createWithContent('file.exe', "MZ\x00\x00"),
        'fake pdf' => UploadedFile::fake()->createWithContent('file.pdf', '<?php echo 1;'),
        'fake image' => UploadedFile::fake()->createWithContent('file.png', '<html>not an image</html>'),
        'unsafe extension' => UploadedFile::fake()->image('image.php'),
        'double extension' => UploadedFile::fake()->image('image.jpg.php'),
        'too large' => UploadedFile::fake()->image('large.png')->size(5121),
    };

    $upload = $kind === 'too large'
        ? $file
        : new UploadedFile($file->getPathname(), $file->getClientOriginalName(), null, UPLOAD_ERR_OK, true);

    $this->post(route('registration.store'), registrationData(['document' => $upload]))
        ->assertSessionHasErrors('document');

    $this->assertDatabaseCount('student_applications', 0);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
})->with(['php', 'html', 'svg', 'executable', 'fake pdf', 'fake image', 'unsafe extension', 'double extension', 'too large']);

test('guest and non administrator requests cannot download documents', function (bool $authenticated) {
    Storage::fake('student_documents');
    $application = StudentApplication::factory()->create([
        'document_path' => 'documents/'.str_repeat('a', 40).'.pdf',
    ]);
    Storage::disk('student_documents')->put($application->document_path, 'private bytes');

    if ($authenticated) {
        $this->actingAs(User::factory()->create());
    }

    $this->get(route('student-applications.document', $application))->assertForbidden();
})->with(['guest' => false, 'non administrator' => true]);

test('admin download refuses missing and unsafe paths', function (?string $path) {
    Storage::fake('student_documents');
    $application = StudentApplication::factory()->create(['document_path' => $path]);
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('student-applications.document', $application))->assertNotFound();
})->with([
    'no document' => null,
    'missing file' => 'documents/'.str_repeat('b', 40).'.pdf',
    'traversal' => '../private/secret.pdf',
    'nested traversal' => 'documents/../secret.pdf',
    'absolute path' => 'C:/secret.pdf',
    'backslashes' => 'documents\\..\\secret.pdf',
    'encoded traversal' => 'documents/%2e%2e/secret.pdf',
]);

test('original filenames never determine the stored path or download name', function () {
    Storage::fake('student_documents');
    $file = UploadedFile::fake()->image('..\\..\\certificate.png');

    $this->post(route('registration.store'), registrationData(['document' => $file]))->assertSessionHasNoErrors();

    $application = StudentApplication::sole();
    expect($application->document_original_name)->toBe('certificate.png');
    Storage::disk('student_documents')->assertExists($application->document_path);
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('student-applications.document', $application))
        ->assertDownload($application->reference_number.'.png');
});

test('failed database insert removes the newly stored document', function () {
    Storage::fake('student_documents');
    $existing = StudentApplication::factory()->create();
    StudentApplication::creating(function (StudentApplication $application) use ($existing): void {
        $application->reference_number = $existing->reference_number;
    });

    $this->post(route('registration.store'), registrationData([
        'document' => UploadedFile::fake()->image('certificate.png'),
    ]))->assertServerError();

    $this->assertDatabaseCount('student_applications', 1);
    Storage::disk('student_documents')->assertDirectoryEmpty('/');
});
