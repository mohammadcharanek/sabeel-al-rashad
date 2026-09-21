<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات الرأي')
                ->description('أدخل رأياً حقيقياً فقط. لا تُدرج بيانات الطلاب أو أرقام الهواتف أو البريد الإلكتروني. الآراء الجديدة غير منشورة افتراضياً.')
                ->schema([
                    Textarea::make('quote')
                        ->label('نص الرأي')
                        ->required()
                        ->maxLength(5000)
                        ->rows(6)
                        ->columnSpanFull(),
                    TextInput::make('person_name')
                        ->label('الاسم المعتمد للعرض العام')
                        ->helperText('استخدم الاسم الذي وافق صاحبه على عرضه فقط.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('person_role')
                        ->label('الصفة (اختياري)')
                        ->placeholder('ولي أمر')
                        ->maxLength(255),
                    TextInput::make('sort_order')
                        ->label('الترتيب')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(4294967295)
                        ->default(0)
                        ->required(),
                    Checkbox::make('is_approved')
                        ->label('أؤكد أن الرأي حقيقي ومعتمد، وأن صاحبه وافق على نشر النص والاسم والصفة المعروضة.')
                        ->helperText('تحقق من استمرار الموافقة عند تعديل المحتوى. لإلغاء الاعتماد، أوقف النشر وأزل هذا التأكيد.')
                        ->default(false)
                        ->accepted(fn (Get $get): bool => (bool) $get('is_published'))
                        ->validationMessages(['accepted' => 'يجب تأكيد صحة الرأي واعتماده وموافقة صاحبه قبل النشر.'])
                        ->columnSpanFull(),
                    Toggle::make('is_published')
                        ->label('نشر الرأي على الموقع')
                        ->helperText('يظهر على الصفحة الرئيسية بعد الاعتماد والموافقة فقط.')
                        ->default(false)
                        ->live(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
