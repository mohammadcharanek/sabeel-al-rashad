<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentApplicationRequest;
use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\EducationSystem;
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
    public function create(Request $request): Response
    {
        $grades = EducationalGrade::with('educationalStage.educationSystem')->availableForRegistration()
            ->orderBy(EducationalStage::select('sort_order')->whereColumn('educational_stages.id', 'educational_grades.educational_stage_id'))
            ->orderBy('educational_stage_id')
            ->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (EducationalGrade $grade): bool => EducationalGrade::codeMatchesCategory($grade->code, $grade->educationalStage->category));
        $systems = EducationSystem::where('is_active', true)
            ->whereIn('id', $grades->pluck('educationalStage.education_system_id')->unique())
            ->orderBy('sort_order')->orderBy('id')->get();
        $systemOrder = $systems->modelKeys();
        $grades = $grades->sortBy(fn (EducationalGrade $grade): int => array_search($grade->educationalStage->education_system_id, $systemOrder, true))->values();
        $requirements = $grades->mapWithKeys(fn (EducationalGrade $grade): array => [
            $grade->id => collect(StudentApplication::REGISTRATION_TYPES)->mapWithKeys(fn (string $label, string $type): array => [
                $type => StudentApplication::registrationRequirements($grade->educationalStage->category, $type, $grade->code),
            ])->all(),
        ])->all();
        $gradeId = $request->old('educational_grade_id');
        $registrationType = $request->old('registration_type');
        $defaultRequirements = StudentApplication::registrationRequirements(null, null);
        $initialRequirements = is_scalar($gradeId) && is_string($registrationType)
            ? ($requirements[$gradeId][$registrationType] ?? $defaultRequirements)
            : $defaultRequirements;

        return response()->view('pages.registration', [
            'siteSettings' => SiteSetting::current(),
            'homepageSettings' => HomepageSetting::current(),
            'grades' => $grades,
            'educationSystems' => $systems,
            'registrationTypes' => StudentApplication::REGISTRATION_TYPES,
            'requirements' => $requirements,
            'initialRequirements' => $initialRequirements,
            'defaultRequirements' => $defaultRequirements,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(StoreStudentApplicationRequest $request): RedirectResponse
    {
        $application = new StudentApplication($request->safe()->except(['document', 'education_system_id']));
        $application->educational_stage_id = EducationalGrade::findOrFail($application->educational_grade_id)->educational_stage_id;
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
