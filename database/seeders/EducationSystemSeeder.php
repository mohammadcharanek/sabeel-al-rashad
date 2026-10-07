<?php

namespace Database\Seeders;

use App\Models\EducationSystem;
use Illuminate\Database\Seeder;

class EducationSystemSeeder extends Seeder
{
    public function run(): void
    {
        EducationSystem::firstOrCreate(['slug' => 'lebanese'], [
            'name' => 'المنهج اللبناني', 'sort_order' => 0, 'is_active' => true,
        ]);
        EducationSystem::firstOrCreate(['slug' => 'american'], [
            'name' => 'المنهج الأميركي', 'sort_order' => 1, 'is_active' => true,
        ]);
    }
}
