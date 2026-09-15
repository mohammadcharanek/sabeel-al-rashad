<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('singleton_key', 20)->default('global')->unique();
            $table->string('school_name_ar');
            $table->string('school_name_en')->nullable();
            $table->text('short_description_ar')->nullable();
            $table->text('short_description_en')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_phone', 32)->nullable();
            $table->string('secondary_phone', 32)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('notification_email')->nullable();
            $table->text('address_ar')->nullable();
            $table->text('address_en')->nullable();
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('whatsapp_number', 16)->nullable();
            $table->text('whatsapp_message_ar')->nullable();
            $table->text('whatsapp_message_en')->nullable();
            $table->string('facebook_url', 2048)->nullable();
            $table->string('instagram_url', 2048)->nullable();
            $table->string('youtube_url', 2048)->nullable();
            $table->string('principal_name_ar')->nullable();
            $table->string('principal_name_en')->nullable();
            $table->string('principal_title_ar')->nullable();
            $table->string('principal_title_en')->nullable();
            $table->text('principal_message_ar')->nullable();
            $table->text('principal_message_en')->nullable();
            $table->string('principal_photo')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->string('social_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
