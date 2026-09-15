<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('homepage_settings')->count() > 1) {
            throw new RuntimeException('Multiple homepage settings records require manual review before adding the singleton constraint.');
        }

        Schema::table('homepage_settings', function (Blueprint $table): void {
            $table->string('singleton_key', 20)->default('global')->unique();
            $table->json('section_visibility')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table): void {
            $table->dropUnique(['singleton_key']);
            $table->dropColumn(['singleton_key', 'section_visibility']);
        });
    }
};
