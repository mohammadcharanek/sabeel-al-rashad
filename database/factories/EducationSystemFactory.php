<?php

namespace Database\Factories;

use App\Models\EducationSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EducationSystem> */
class EducationSystemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'slug' => fake()->unique()->slug(),
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function american(): static
    {
        return $this->state(fn (): array => ['name' => 'المنهج الأميركي', 'slug' => 'american', 'sort_order' => 1]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
