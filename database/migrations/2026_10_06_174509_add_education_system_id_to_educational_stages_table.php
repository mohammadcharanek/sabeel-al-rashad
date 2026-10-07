<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educational_stages', function (Blueprint $table): void {
            $table->foreignId('education_system_id')->nullable()->constrained()->restrictOnDelete();
        });

        DB::table('education_systems')->insertOrIgnore([
            'name' => 'المنهج اللبناني',
            'slug' => 'lebanese',
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /**
         * The pre-system stages use the national categories. Preserve unknown and
         * unclassified rows for explicit administrator assignment, without guessing.
         * Historical migrations deliberately use fixed values rather than models.
         */
        DB::table('educational_stages')
            ->whereNull('education_system_id')
            ->whereIn('category', ['kindergarten', 'basic', 'primary', 'intermediate', 'secondary'])
            ->update(['education_system_id' => DB::table('education_systems')->where('slug', 'lebanese')->value('id')]);
    }

    public function down(): void
    {
        Schema::table('educational_stages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('education_system_id');
        });
    }
};
