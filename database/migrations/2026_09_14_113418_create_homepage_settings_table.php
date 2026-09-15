<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('homepage_settings', function (Blueprint $table) {
        $table->id();

        /*
        |--------------------------------------------------------------------------
        | Hero
        |--------------------------------------------------------------------------
        */

        $table->string('hero_eyebrow')->nullable();
        $table->string('hero_title')->nullable();
        $table->text('hero_description')->nullable();

        $table->string('hero_image_desktop')->nullable();
        $table->string('hero_image_mobile')->nullable();

        $table->string('hero_primary_label')->nullable();
        $table->string('hero_primary_url')->nullable();

        $table->string('hero_secondary_label')->nullable();
        $table->string('hero_secondary_url')->nullable();

        /*
        |--------------------------------------------------------------------------
        | Introduction
        |--------------------------------------------------------------------------
        */

        $table->string('intro_eyebrow')->nullable();
        $table->string('intro_title')->nullable();

        $table->text('intro_paragraph_one')->nullable();
        $table->text('intro_paragraph_two')->nullable();

        $table->string('intro_image')->nullable();
        $table->string('experience_label')->nullable();

        /*
        |--------------------------------------------------------------------------
        | Principal Message
        |--------------------------------------------------------------------------
        */

        $table->string('principal_eyebrow')->nullable();

        $table->text('principal_message')->nullable();

        $table->string('principal_name')->nullable();
        $table->string('principal_title')->nullable();
        $table->string('principal_image')->nullable();

        /*
        |--------------------------------------------------------------------------
        | Admissions CTA
        |--------------------------------------------------------------------------
        */

        $table->string('admissions_eyebrow')->nullable();
        $table->string('admissions_title')->nullable();

        $table->text('admissions_description')->nullable();

        $table->string('admissions_primary_label')->nullable();
        $table->string('admissions_primary_url')->nullable();

        $table->string('admissions_secondary_label')->nullable();
        $table->string('admissions_secondary_url')->nullable();

        /*
        |--------------------------------------------------------------------------
        | SEO
        |--------------------------------------------------------------------------
        */

        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
