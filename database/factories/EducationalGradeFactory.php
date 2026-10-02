<?php

namespace Database\Factories;

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EducationalGrade>
 */
class EducationalGradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'educational_stage_id' => EducationalStage::factory(),
            'name_ar' => 'الصف '.fake()->unique()->numberBetween(1, 10000),
            'code' => 'grade_'.fake()->unique()->numberBetween(1, 10000),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
