<?php

use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('approval migration preserves all legacy fields and requires explicit approval for every old record', function () {
    config(['database.connections.testimonial_upgrade' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    $connection = DB::connection('testimonial_upgrade');
    $originalSchema = Schema::getFacadeRoot();
    Schema::swap($connection->getSchemaBuilder());

    try {
        $originalMigration = require database_path('migrations/2026_09_14_113408_create_testimonials_table.php');
        $originalMigration->up();
        $legacy = Testimonial::factory()->raw([
            'id' => 71, 'person_name' => null, 'person_role' => null,
            'avatar' => 'legacy/avatar.jpg', 'rating' => 2, 'sort_order' => 5,
            'created_at' => '2026-09-14 12:00:00', 'updated_at' => '2026-09-15 12:00:00',
        ]);
        unset($legacy['is_approved']);
        $connection->table('testimonials')->insert([
            $legacy,
            array_replace($legacy, ['id' => 72, 'person_name' => 'Legacy display name', 'is_published' => true]),
        ]);
        $columns = array_keys($legacy);
        $before = $connection->table('testimonials')->orderBy('id')->get($columns)->toArray();

        $migration = require database_path('migrations/2026_09_21_114055_add_is_approved_to_testimonials_table.php');
        $migration->up();

        expect($connection->table('testimonials')->orderBy('id')->get($columns)->toArray())->toEqual($before);
        expect($connection->table('testimonials')->where('is_approved', false)->count())->toBe(2);
        expect(Testimonial::on('testimonial_upgrade')->published()->count())->toBe(0);

        $id = $connection->table('testimonials')->insertGetId(['quote' => 'Database default test']);
        $this->assertDatabaseHas('testimonials', ['id' => $id, 'is_published' => false, 'is_approved' => false], 'testimonial_upgrade');
    } finally {
        Schema::swap($originalSchema);
        DB::purge('testimonial_upgrade');
    }
});
