<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SiteSetting> */
class SiteSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_name_ar' => 'ثانوية سبيل الرشاد',
            'contact_email' => fake()->safeEmail(),
        ];
    }
}
