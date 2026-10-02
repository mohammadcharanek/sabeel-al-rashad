<?php

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use App\Models\StudentApplication;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/** @param array<string, mixed> $overrides */
function registrationData(array $overrides = []): array
{
    if (! array_key_exists('educational_grade_id', $overrides)) {
        $stage = isset($overrides['educational_stage_id']) ? EducationalStage::find($overrides['educational_stage_id']) : null;
        $grade = $stage
            ? EducationalGrade::factory()->for($stage, 'educationalStage')->create(['code' => $stage->category === 'kindergarten' ? 'kg2' : 'grade_1'])
            : EducationalGrade::factory()->create();
        $overrides['educational_grade_id'] = $grade->id;
    }

    return StudentApplication::factory()->raw(array_replace([
        'guardian_name' => 'محمد حسن',
        'guardian_phone' => '03 123 456',
        'educational_stage_id' => EducationalStage::factory(),
    ], $overrides));
}
