<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الصورة والمعلومات')
                ->description('استخدم صوراً تملك المدرسة إذن نشرها. لن تظهر الصورة على الموقع حتى تفعيل النشر.')
                ->schema([
                    FileUpload::make('image')
                        ->label('الصورة')
                        ->image()
                        ->preventFilePathTampering()
                        ->rules(['extensions:jpg,jpeg,png,webp'])
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->disk('public')
                        ->directory('gallery')
                        ->visibility('public')
                        ->maxSize(5120)
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('title')->label('عنوان الصورة')->maxLength(255),
                    TextInput::make('alt_text')->label('وصف الصورة لقارئات الشاشة')->required()->maxLength(255),
                    Textarea::make('caption')->label('تعليق الصورة')->rows(3)->columnSpanFull(),
                    TextInput::make('category')->label('التصنيف')->maxLength(255),
                    TextInput::make('sort_order')->label('الترتيب')->integer()->required()->minValue(0)->maxValue(4294967295)->default(0),
                    Toggle::make('is_featured')->label('صورة رئيسية')->default(false),
                    Select::make('media_folder_id')->label('مجلد الصور')
                        ->relationship('folder', 'title', fn (Builder $query): Builder => $query->where('media_type', 'photo')->orderBy('sort_order')->orderBy('id'))
                        ->searchable()->preload()->placeholder('صور غير مصنفة')
                        ->rules([Rule::exists('media_folders', 'id')->where('media_type', 'photo')]),
                    Toggle::make('is_active')->label('اعتماد الصورة ونشرها')->default(false)
                        ->helperText('بتفعيل النشر تؤكد اعتماد الصورة والحصول على إذن نشر صور الطلاب. إلغاء النشر يخفيها من المعرض؛ الملفات على القرص العام وتبقى متاحة عبر روابطها المباشرة.'),
                ])->columns(2),
        ]);
    }
}
