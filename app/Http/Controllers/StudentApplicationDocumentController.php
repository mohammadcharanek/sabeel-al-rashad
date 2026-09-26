<?php

namespace App\Http\Controllers;

use App\Models\StudentApplication;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentApplicationDocumentController extends Controller
{
    public function __invoke(StudentApplication $studentApplication): StreamedResponse
    {
        Gate::authorize('downloadDocument', $studentApplication);

        $path = $studentApplication->document_path;
        abort_unless(is_string($path) && preg_match('/\Adocuments\/[a-zA-Z0-9]{40}\.(pdf|jpg|jpeg|png)\z/', $path), 404);

        $disk = Storage::disk('student_documents');
        abort_unless($disk->exists($path), 404);

        return $disk->download($path, $studentApplication->reference_number.'.'.pathinfo($path, PATHINFO_EXTENSION), [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
