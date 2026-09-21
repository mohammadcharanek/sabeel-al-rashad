<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'quote' => 'نص مخصص للاختبار الآلي فقط.',
            'person_name' => 'اسم اختبار',
            'person_role' => null,
            'sort_order' => 0,
            'is_published' => false,
            'is_approved' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => ['is_approved' => true]);
    }

    public function published(): static
    {
        return $this->approved()->state(fn (): array => ['is_published' => true]);
    }
}
