<?php

namespace App\Filament\Resources\MediaFolders\Schemas;

use App\Models\MediaFolder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaFolderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات المجلد')
                ->description('إخفاء المجلد يخفي محتوياته من المعارض والصفحة الرئيسية. أعد تعيين محتوياته قبل حذفه أو تغيير نوعه. لا تُحذف الملفات عند حذف المجلد.')
                ->schema([
                    TextInput::make('title')->label('العنوان بالعربية')->required()->maxLength(255),
                    TextInput::make('slug')->label('الرابط المختصر')->required()->maxLength(255)
                        ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')->unique(ignoreRecord: true)
                        ->helperText('حروف إنجليزية صغيرة وأرقام وشرطات، مثل school-events.'),
                    Select::make('media_type')->label('نوع الوسائط')->options(['photo' => 'صور', 'video' => 'فيديو'])
                        ->required()->default('photo')->in(['photo', 'video'])
                        ->disabled(fn (?MediaFolder $record): bool => $record?->hasMedia() ?? false),
                    TextInput::make('sort_order')->label('الترتيب')->integer()->required()->minValue(0)->maxValue(4294967295)->default(0),
                    Textarea::make('description')->label('الوصف')->rows(3)->maxLength(5000)->columnSpanFull(),
                    FileUpload::make('cover')->label('غلاف المجلد')->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->rules(['extensions:jpg,jpeg,png,webp'])->maxSize(5120)
                        ->disk('public')->directory('media-covers')->visibility('public')
                        ->preventFilePathTampering()->columnSpanFull(),
                    Toggle::make('is_published')->label('اعتماد المجلد ونشره')->default(false)
                        ->helperText('اعتمد الغلاف واحصل على إذن نشر صور الطلاب قبل النشر. الملفات على القرص العام؛ إخفاء المجلد لا يمنع الوصول إلى روابط الملفات المباشرة.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
