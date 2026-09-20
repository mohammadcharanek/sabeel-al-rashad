<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الفيديو والمعلومات')
                ->description('انشر فقط محتوى معتمداً تملك المدرسة إذن نشره، خاصة عند ظهور الطلاب. الملفات محفوظة على القرص العام ويمكن الوصول إليها عبر روابطها المباشرة حتى قبل النشر.')
                ->schema([
                    TextInput::make('title')->label('عنوان الفيديو')->required()->maxLength(255),
                    Select::make('media_folder_id')->label('مجلد الفيديو')
                        ->relationship('folder', 'title', fn (Builder $query): Builder => $query->where('media_type', 'video')->orderBy('sort_order')->orderBy('id'))
                        ->searchable()->preload()->placeholder('فيديو غير مصنف')
                        ->rules([Rule::exists('media_folders', 'id')->where('media_type', 'video')]),
                    Textarea::make('description')->label('الوصف / ملخص المحتوى')->rows(4)->maxLength(10000)->columnSpanFull(),
                    FileUpload::make('video')->label('ملف الفيديو')->required()
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])->rules(['extensions:mp4,webm'])
                        ->maxSize(102400)->disk('public')->directory('videos')->visibility('public')
                        ->preventFilePathTampering()->columnSpanFull()
                        ->helperText('MP4 أو WebM، بحد أقصى 100 ميغابايت. استخدم ترميزاً متوافقاً مع المتصفحات مثل H.264/AAC في MP4 أو VP9/Opus في WebM.'),
                    FileUpload::make('poster')->label('صورة معاينة الفيديو')->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->rules(['extensions:jpg,jpeg,png,webp'])
                        ->maxSize(5120)->disk('public')->directory('video-posters')->visibility('public')
                        ->preventFilePathTampering()->columnSpanFull(),
                    TextInput::make('sort_order')->label('الترتيب')->integer()->required()->minValue(0)->maxValue(4294967295)->default(0),
                    Toggle::make('is_published')->label('اعتماد الفيديو ونشره')->default(false)
                        ->helperText('بتفعيل النشر تؤكد مراجعة الفيديو وصورة المعاينة والحصول على إذن نشر المحتوى الذي يظهر فيه الطلاب.'),
                ])->columns(2),
        ]);
    }
}
