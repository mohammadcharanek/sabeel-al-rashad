<?php

namespace Database\Factories;

use App\Models\EducationalStage;
use App\Models\EducationSystem;
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
            'education_system_id' => fn (): int => EducationSystem::firstOrCreate(['slug' => 'lebanese'], ['name' => 'المنهج اللبناني', 'sort_order' => 0, 'is_active' => true])->id,
            'title' => $title,
            'category' => 'basic',
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 999999),
            'description' => fake()->sentence(14),
            'icon' => fn (array $attributes): string => $attributes['category'] ?? 'basic',
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

    public function american(string $category = 'elementary'): static
    {
        return $this->state(fn (): array => [
            'education_system_id' => EducationSystem::firstOrCreate(['slug' => 'american'], ['name' => 'المنهج الأميركي', 'sort_order' => 1, 'is_active' => true])->id,
            'category' => $category,
            'icon' => $category === 'elementary' ? 'american_elementary' : $category,
        ]);
    }
}
