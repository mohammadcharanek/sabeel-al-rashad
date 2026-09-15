<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\EditsSingletonSettings;
use App\Filament\Schemas\SettingsFields;
use App\Models\HomepageSetting;
use App\Models\SingletonSetting;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class HomepageSettings extends Page
{
    use EditsSingletonSettings;

    protected string $view = 'filament.pages.homepage-settings';

    protected static ?string $title = 'إعدادات الصفحة الرئيسية';

    protected ?string $subheading = 'تظهر التعديلات المحفوظة على الصفحة الرئيسية. تُستخدم النصوص والصور الافتراضية عند ترك الحقول فارغة.';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 2;

    protected function settings(): SingletonSetting
    {
        return HomepageSetting::current();
    }

    /** @return array<string, mixed> */
    protected function settingsFormData(SingletonSetting $settings): array
    {
        $data = $settings->attributesToArray();

        foreach (HomepageSetting::SECTIONS as $section) {
            $data['section_visibility'][$section] = data_get($data, "section_visibility.{$section}") !== false;
        }

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->settings())
            ->statePath('data')
            ->components([
                Section::make('الواجهة الرئيسية')->columns(2)->schema([
                    SettingsFields::text('hero_eyebrow', 'النص التمهيدي'),
                    SettingsFields::text('hero_title', 'العنوان'),
                    SettingsFields::paragraph('hero_description', 'الوصف'),
                    SettingsFields::image('hero_image_desktop', 'صورة سطح المكتب', 'homepage'),
                    SettingsFields::image('hero_image_mobile', 'صورة الهاتف', 'homepage'),
                    SettingsFields::text('hero_primary_label', 'نص الزر الأساسي'),
                    SettingsFields::url('hero_primary_url', 'رابط الزر الأساسي', 255),
                    SettingsFields::text('hero_secondary_label', 'نص الزر الإضافي'),
                    SettingsFields::url('hero_secondary_url', 'رابط الزر الإضافي', 255),
                ]),
                Section::make('التعريف بالمدرسة')->columns(2)->schema([
                    SettingsFields::text('intro_eyebrow', 'النص التمهيدي'),
                    SettingsFields::text('intro_title', 'العنوان'),
                    SettingsFields::paragraph('intro_paragraph_one', 'الفقرة الأولى'),
                    SettingsFields::paragraph('intro_paragraph_two', 'الفقرة الثانية'),
                    SettingsFields::image('intro_image', 'الصورة التعريفية', 'homepage'),
                    SettingsFields::text('experience_label', 'نص سنوات الخبرة'),
                ]),
                Section::make('كلمة المدير في الصفحة الرئيسية')
                    ->description('يُعدّل اسم المدير وصفته وكلمته وصورته من إعدادات الموقع.')
                    ->schema([
                        SettingsFields::text('principal_eyebrow', 'النص التمهيدي'),
                    ]),
                Section::make('دعوة التسجيل')->columns(2)->schema([
                    SettingsFields::text('admissions_eyebrow', 'النص التمهيدي'),
                    SettingsFields::text('admissions_title', 'العنوان'),
                    SettingsFields::paragraph('admissions_description', 'الوصف'),
                    SettingsFields::text('admissions_primary_label', 'نص الزر الأساسي'),
                    SettingsFields::url('admissions_primary_url', 'رابط الزر الأساسي', 255),
                    SettingsFields::text('admissions_secondary_label', 'نص الزر الإضافي'),
                    SettingsFields::url('admissions_secondary_url', 'رابط الزر الإضافي', 255),
                ]),
                Section::make('إعدادات SEO')->columns(2)->schema([
                    SettingsFields::text('meta_title', 'عنوان نتائج البحث'),
                    SettingsFields::paragraph('meta_description', 'وصف نتائج البحث'),
                ]),
                Section::make('ظهور الأقسام')->columns(2)->schema([
                    Toggle::make('section_visibility.hero')->label('الواجهة الرئيسية')->default(true),
                    Toggle::make('section_visibility.intro')->label('التعريف بالمدرسة')->default(true),
                    Toggle::make('section_visibility.statistics')->label('الإحصائيات')->default(true),
                    Toggle::make('section_visibility.stages')->label('المراحل التعليمية')->default(true),
                    Toggle::make('section_visibility.why-us')->label('مميزات المدرسة')->default(true),
                    Toggle::make('section_visibility.school-life')->label('الحياة المدرسية')->default(true),
                    Toggle::make('section_visibility.news')->label('الأخبار')->default(true),
                    Toggle::make('section_visibility.events')->label('الفعاليات')->default(true),
                    Toggle::make('section_visibility.principal-message')->label('كلمة المدير')->default(true),
                    Toggle::make('section_visibility.testimonials')->label('آراء أولياء الأمور')->default(true),
                    Toggle::make('section_visibility.admissions-cta')->label('دعوة التسجيل')->default(true),
                ]),
            ]);
    }
}
