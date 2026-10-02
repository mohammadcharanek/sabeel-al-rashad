<?php

namespace Database\Factories;

use App\Models\EducationalStage;
use App\Models\StudentApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudentApplication> */
class StudentApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_name' => 'أحمد محمد حسن',
            'date_of_birth' => '2015-03-12',
            'educational_stage_id' => EducationalStage::factory(),
            'registration_type' => 'current_student',
            'guardian_name' => fake()->name(),
            'guardian_phone' => '03'.fake()->numerify('######'),
            'guardian_email' => null,
            'notes' => null,
        ];
    }
}
