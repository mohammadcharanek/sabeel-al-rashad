<?php

namespace Database\Factories;

use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'alt_text' => fake()->sentence(),
            'image' => 'gallery/'.fake()->uuid().'.jpg',
            'sort_order' => 0,
            'is_active' => false,
            'is_featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }
}
