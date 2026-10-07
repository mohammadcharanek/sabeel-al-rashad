<?php

use App\Models\StudentApplication;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('education system upgrade and rollback preserve national IDs categories grades and applications', function () {
    DB::connection()->getPdo();
    $originalConnection = DB::getDefaultConnection();
    config(['database.connections.system_upgrade' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]]);
    DB::setDefaultConnection('system_upgrade');

    try {
        foreach (['2026_09_14_113313_create_educational_stages_table.php', '2026_09_26_194452_create_student_applications_table.php',
            '2026_10_01_230556_add_category_to_educational_stages_table.php', '2026_10_01_230558_add_registration_type_to_student_applications_table.php',
            '2026_10_02_222157_create_educational_grades_table.php', '2026_10_02_222158_add_educational_grade_id_to_student_applications_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        foreach (['kindergarten', 'basic', 'primary', 'intermediate', 'secondary', null, 'legacy'] as $index => $category) {
            DB::table('educational_stages')->insert([
                'id' => 40 + $index, 'title' => 'Legacy '.$index, 'slug' => 'legacy-'.$index,
                'category' => $category, 'icon' => 'elementary', 'image' => 'legacy/stage.jpg',
                'sort_order' => $index, 'is_active' => false,
            ]);
        }
        DB::table('educational_grades')->insert([
            'id' => 71, 'educational_stage_id' => 41, 'name_ar' => 'الصف الأول', 'code' => 'grade_1',
        ]);
        StudentApplication::factory()->create([
            'educational_stage_id' => 41, 'educational_grade_id' => 71,
            'document_path' => 'documents/legacy.pdf', 'admin_note' => 'ملاحظة قديمة',
        ]);
        StudentApplication::factory()->create(['educational_stage_id' => 45, 'educational_grade_id' => null]);
        $stages = DB::table('educational_stages')->orderBy('id')->get();
        $grades = DB::table('educational_grades')->get();
        $applications = DB::table('student_applications')->get();
        $systemsMigration = require database_path('migrations/2026_10_06_174508_create_education_systems_table.php');
        $stageMigration = require database_path('migrations/2026_10_06_174509_add_education_system_id_to_educational_stages_table.php');

        $systemsMigration->up();
        $stageMigration->up();

        $system = DB::table('education_systems')->sole();
        expect($system->slug)->toBe('lebanese');
        expect($system->name)->toBe('المنهج اللبناني');
        expect(DB::table('educational_stages')->where('education_system_id', $system->id)->orderBy('id')->pluck('id')->all())
            ->toBe([40, 41, 42, 43, 44]);
        expect(DB::table('educational_stages')->whereNull('education_system_id')->orderBy('id')->pluck('id')->all())->toBe([45, 46]);
        expect(DB::table('educational_stages')->orderBy('id')->get(array_keys((array) $stages->first())))->toEqual($stages);
        expect(DB::table('educational_grades')->get())->toEqual($grades);
        expect(DB::table('student_applications')->get())->toEqual($applications);
        expect(StudentApplication::with('educationalStage.educationSystem', 'educationalGrade')->first()->educationalGrade->code)->toBe('grade_1');
        expect(fn () => DB::table('education_systems')->where('id', $system->id)->delete())->toThrow(QueryException::class);
        expect(fn () => DB::table('educational_stages')->where('id', 40)->update(['education_system_id' => 999999]))->toThrow(QueryException::class);

        $admissionMigration = require database_path('migrations/2026_10_07_125124_add_document_details_to_student_applications_table.php');
        $admissionMigration->up();

        expect(DB::table('student_applications')->get(array_keys((array) $applications->first())))->toEqual($applications);
        $legacy = StudentApplication::first();
        expect($legacy->document_type)->toBeNull();
        expect($legacy->foreign_document_attestation_confirmed)->toBeNull();
        expect($legacy->document_path)->toBe('documents/legacy.pdf');
        expect(DB::table('student_applications')->whereNotNull('foreign_document_attestation_confirmed')->count())->toBe(0);

        $admissionMigration->down();

        expect(Schema::hasColumn('student_applications', 'document_type'))->toBeFalse();
        expect(Schema::hasColumn('student_applications', 'foreign_document_attestation_confirmed'))->toBeFalse();
        expect(DB::table('student_applications')->get())->toEqual($applications);

        $stageMigration->down();
        $systemsMigration->down();

        expect(Schema::hasTable('education_systems'))->toBeFalse();
        expect(DB::table('educational_stages')->orderBy('id')->get())->toEqual($stages);
        expect(DB::table('educational_grades')->get())->toEqual($grades);
        expect(DB::table('student_applications')->get())->toEqual($applications);
    } finally {
        DB::setDefaultConnection($originalConnection);
        DB::purge('system_upgrade');
    }
});
