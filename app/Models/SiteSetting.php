<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiteSetting extends SingletonSetting
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'singleton_key',
        'school_name_ar',
        'school_name_en',
        'short_description_ar',
        'short_description_en',
        'logo',
        'favicon',
        'primary_phone',
        'secondary_phone',
        'contact_email',
        'notification_email',
        'address_ar',
        'address_en',
        'whatsapp_enabled',
        'whatsapp_number',
        'whatsapp_message_ar',
        'whatsapp_message_en',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'principal_name_ar',
        'principal_name_en',
        'principal_title_ar',
        'principal_title_en',
        'principal_message_ar',
        'principal_message_en',
        'principal_photo',
        'meta_title_ar',
        'meta_title_en',
        'meta_description_ar',
        'meta_description_en',
        'social_image',
    ];

    protected $attributes = [
        'singleton_key' => 'global',
        'school_name_ar' => 'ثانوية سبيل الرشاد',
        'whatsapp_enabled' => false,
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_enabled' => 'boolean',
        ];
    }
}
