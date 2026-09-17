<?php

namespace App\Filament\Resources\Statistics\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StatisticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الإحصائية')
                ->description('أدخل أرقاماً معتمدة من إدارة المدرسة فقط. لا تُنشر الإحصائية حتى تفعيل الظهور.')
                ->schema([
                    TextInput::make('value')
                        ->label('القيمة المعتمدة')
                        ->required()
                        ->maxLength(255)
                        ->helperText('مثال: ٥٠٠+، بعد التحقق من الرقم لدى إدارة المدرسة.'),
                    TextInput::make('label')
                        ->label('وصف الإحصائية')
                        ->required()
                        ->maxLength(255),
                    Select::make('icon')
                        ->label('الأيقونة')
                        ->options([
                            'users' => 'الطلاب',
                            'teachers' => 'المعلمون',
                            'history' => 'الخبرة',
                            'activity' => 'الأنشطة',
                        ])
                        ->default('users'),
                    TextInput::make('sort_order')
                        ->label('الترتيب')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(4294967295)
                        ->default(0)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('نشر الإحصائية على الموقع')
                        ->default(false),
                ])
                ->columns(2),
        ]);
    }
}
