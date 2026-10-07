<?php

namespace Database\Factories;

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

/**
 * @extends Factory<EducationalGrade>
 */
class EducationalGradeFactory extends Factory
{
    public function american(int $number = 1): static
    {
        foreach (EducationalStage::AMERICAN_GRADE_RANGES as $category => [$first, $last]) {
            if ($number >= $first && $number <= $last) {
                return $this->state(fn (): array => [
                    'educational_stage_id' => EducationalStage::factory()->american($category),
                    'code' => 'american_grade_'.$number,
                    'name_ar' => 'Grade '.$number,
                    'sort_order' => $number,
                ]);
            }
        }

        throw new InvalidArgumentException('American grades must be between 1 and 12.');
    }

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
