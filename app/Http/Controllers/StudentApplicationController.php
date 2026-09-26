<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentApplicationRequest;
use App\Models\EducationalStage;
use App\Models\HomepageSetting;
use App\Models\SiteSetting;
use App\Models\StudentApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class StudentApplicationController extends Controller
{
    public function create(): Response
    {
        return response()->view('pages.registration', [
            'siteSettings' => SiteSetting::current(),
            'homepageSettings' => HomepageSetting::current(),
            'stages' => EducationalStage::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(StoreStudentApplicationRequest $request): RedirectResponse
    {
        $application = new StudentApplication($request->safe()->except('document'));
        $documentPath = null;

        try {
            if ($document = $request->file('document')) {
                $documentPath = $document->store('documents', 'student_documents');
                $application->document_path = $documentPath;
                $application->document_original_name = Str::limit(
                    basename(str_replace('\\', '/', $document->getClientOriginalName())),
                    255,
                    '',
                );
            }

            $application->save();
        } catch (Throwable $exception) {
            if ($documentPath !== null) {
                Storage::disk('student_documents')->delete($documentPath);
            }

            throw $exception;
        }

        $request->session()->forget('_old_input');
        $request->session()->put('registration_reference', $application->reference_number);

        return redirect()->route('registration.confirmation');
    }

    public function confirmation(Request $request): Response|RedirectResponse
    {
        $reference = $request->session()->get('registration_reference');

        if (! is_string($reference)) {
            return redirect()->route('registration.create');
        }

        return response()->view('pages.registration-confirmation', [
            'siteSettings' => SiteSetting::current(),
            'homepageSettings' => HomepageSetting::current(),
            'reference' => $reference,
        ])->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
