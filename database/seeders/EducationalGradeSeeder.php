<?php

namespace Database\Seeders;

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use Illuminate\Database\Seeder;

class EducationalGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = EducationalStage::where('category', 'kindergarten')->get();

        if ($stages->count() !== 1) {
            return;
        }

        foreach (EducationalGrade::KINDERGARTEN_CODES as $index => $code) {
            EducationalGrade::firstOrCreate(['code' => $code], [
                'educational_stage_id' => $stages->sole()->id,
                'name_ar' => 'الروضة '.($index + 1),
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
