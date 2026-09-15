<?php

namespace Database\Factories;

use App\Models\EducationalStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EducationalStage>
 */
class EducationalStageFactory extends Factory
{
    protected $model = EducationalStage::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 999999),
            'description' => fake()->sentence(14),
            'icon' => 'elementary',
            'image' => null,
            'link_label' => 'تعرف أكثر',
            'link_url' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}