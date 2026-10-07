<?php

namespace Database\Seeders;

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\EducationSystem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AmericanEducationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call(EducationSystemSeeder::class);
            $system = EducationSystem::where('slug', 'american')->sole();

            foreach (EducationalStage::AMERICAN_GRADE_RANGES as $category => [$first, $last]) {
                $stage = EducationalStage::firstOrCreate(['slug' => 'american-'.str_replace('_', '-', $category)], [
                    'education_system_id' => $system->id,
                    'category' => $category,
                    'title' => EducationalStage::CATEGORIES[$category],
                    'icon' => $category === 'elementary' ? 'american_elementary' : $category,
                    'sort_order' => $first,
                    'is_active' => true,
                ]);

                if ($stage->education_system_id !== $system->id || $stage->category !== $category) {
                    throw ValidationException::withMessages(['slug' => 'رمز المرحلة الأميركية مستخدم لمرحلة أخرى. لم يتم تغيير البيانات الموجودة.']);
                }

                foreach (range($first, $last) as $number) {
                    $grade = EducationalGrade::firstOrCreate(['code' => 'american_grade_'.$number], [
                        'educational_stage_id' => $stage->id,
                        'name_ar' => 'Grade '.$number,
                        'sort_order' => $number,
                        'is_active' => true,
                    ]);

                    if ($grade->educational_stage_id !== $stage->id) {
                        throw ValidationException::withMessages(['code' => 'رمز الصف الأميركي مستخدم في مرحلة أخرى. لم يتم تغيير البيانات الموجودة.']);
                    }
                }
            }
        });
    }
}
