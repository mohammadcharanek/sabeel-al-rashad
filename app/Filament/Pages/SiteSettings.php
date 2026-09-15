<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\EditsSingletonSettings;
use App\Filament\Schemas\SettingsFields;
use App\Models\SingletonSetting;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SiteSettings extends Page
{
    use EditsSingletonSettings;

    protected string $view = 'filament.pages.site-settings';

    protected static ?string $title = 'إعدادات المدرسة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 1;

    protected function settings(): SingletonSetting
    {
        return SiteSetting::current();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->settings())
            ->statePath('data')
            ->components([
                Section::make('هوية المدرسة')->columns(2)->schema([
                    SettingsFields::text('school_name_ar', 'اسم المدرسة بالعربية')->required(),
                    SettingsFields::text('school_name_en', 'اسم المدرسة بالإنجليزية'),
                    SettingsFields::paragraph('short_description_ar', 'وصف مختصر بالعربية'),
                    SettingsFields::paragraph('short_description_en', 'وصف مختصر بالإنجليزية'),
                    SettingsFields::image('logo', 'شعار المدرسة', 'settings'),
                    SettingsFields::image('favicon', 'أيقونة الموقع', 'settings')->maxSize(1024),
                ]),
                Section::make('معلومات التواصل')->columns(2)->schema([
                    SettingsFields::text('primary_phone', 'رقم الهاتف الأساسي', 32)->tel(),
                    SettingsFields::text('secondary_phone', 'رقم هاتف إضافي', 32)->tel(),
                    SettingsFields::text('contact_email', 'البريد الإلكتروني للتواصل')->email(),
                    SettingsFields::text('notification_email', 'بريد استقبال التنبيهات')->email(),
                    SettingsFields::paragraph('address_ar', 'العنوان بالعربية'),
                    SettingsFields::paragraph('address_en', 'العنوان بالإنجليزية'),
                ]),
                Section::make('واتساب')->columns(2)->schema([
                    Toggle::make('whatsapp_enabled')->label('تفعيل التواصل عبر واتساب')->default(false)->live(),
                    SettingsFields::text('whatsapp_number', 'رقم واتساب الدولي', 32)
                        ->tel()
                        ->placeholder('+96170123456')
                        ->helperText('أدخل رقم الدولة والرقم كاملاً. يُحفظ الرقم بصيغة دولية موحدة.')
                        ->requiredIf('whatsapp_enabled', true)
                        ->mutateStateForValidationUsing(fn (?string $state): ?string => SettingsFields::normalizePhone($state))
                        ->dehydrateStateUsing(fn (?string $state): ?string => SettingsFields::normalizePhone($state))
                        ->regex('/^\\+[1-9][0-9]{7,14}$/D'),
                    SettingsFields::paragraph('whatsapp_message_ar', 'الرسالة الافتراضية بالعربية'),
                    SettingsFields::paragraph('whatsapp_message_en', 'الرسالة الافتراضية بالإنجليزية'),
                ]),
                Section::make('مدير المدرسة')->columns(2)->schema([
                    SettingsFields::text('principal_name_ar', 'اسم المدير بالعربية'),
                    SettingsFields::text('principal_name_en', 'اسم المدير بالإنجليزية'),
                    SettingsFields::text('principal_title_ar', 'الصفة بالعربية'),
                    SettingsFields::text('principal_title_en', 'الصفة بالإنجليزية'),
                    SettingsFields::paragraph('principal_message_ar', 'كلمة المدير بالعربية'),
                    SettingsFields::paragraph('principal_message_en', 'كلمة المدير بالإنجليزية'),
                    SettingsFields::image('principal_photo', 'صورة المدير', 'settings'),
                ]),
                Section::make('وسائل التواصل الاجتماعي')->columns(2)->schema([
                    SettingsFields::url('facebook_url', 'رابط فيسبوك'),
                    SettingsFields::url('instagram_url', 'رابط إنستغرام'),
                    SettingsFields::url('youtube_url', 'رابط يوتيوب'),
                ]),
                Section::make('إعدادات SEO')->columns(2)->schema([
                    SettingsFields::text('meta_title_ar', 'عنوان نتائج البحث بالعربية'),
                    SettingsFields::text('meta_title_en', 'عنوان نتائج البحث بالإنجليزية'),
                    SettingsFields::paragraph('meta_description_ar', 'وصف نتائج البحث بالعربية')->maxLength(500),
                    SettingsFields::paragraph('meta_description_en', 'وصف نتائج البحث بالإنجليزية')->maxLength(500),
                    SettingsFields::image('social_image', 'صورة المشاركة الافتراضية', 'settings'),
                ]),
            ]);
    }
}
