<x-layouts.app :site-settings="$siteSettings" :homepage-settings="$homepageSettings" :title="'طلب تسجيل طالب | '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد')">
    <div class="bg-brand-navy px-4 py-12 text-center text-white md:py-16">
        <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center text-sm underline underline-offset-4">الرئيسية</a>
        <h1 class="mt-3 text-3xl font-black md:text-4xl">طلب تسجيل طالب</h1>
        <p class="mx-auto mt-4 max-w-xl leading-8 text-white/80">أهلاً بكم في {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}. يرجى تعبئة البيانات التالية لتقديم طلب التسجيل.</p>
    </div>

    <section class="mx-auto max-w-3xl px-4 py-10 md:px-8 md:py-14" aria-label="استمارة التسجيل">
        <p class="mb-6 rounded-xl bg-surface-card p-4 text-sm leading-7">تُستخدم بيانات الطلب والمستند المرفق لمراجعة التسجيل من قبل إدارة المدرسة فقط. تقديم الطلب لا يعني القبول النهائي. ستحصلون على رقم مرجع بعد الإرسال.</p>

        @if ($errors->any())
            <div role="alert" tabindex="-1" class="mb-6 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
                <h2 class="font-bold">يرجى تصحيح البيانات التالية:</h2>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->messages() as $field => $messages)
                        <li><a href="#{{ $field }}" class="underline underline-offset-4">{{ $messages[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($stages->isEmpty())
            <p role="status" class="rounded-xl border border-border-card p-6 leading-8">لا توجد مراحل متاحة للتسجيل حالياً. يرجى التواصل مع إدارة المدرسة.</p>
        @else
            <form method="POST" action="{{ route('registration.store') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <fieldset class="min-w-0">
                    <legend class="mb-5 text-xl font-bold text-brand-navy">بيانات الطالب</legend>
                    <div class="grid min-w-0 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-site.registration-field name="student_name" label="اسم الطالب الثلاثي باللغة العربية" required maxlength="150" autocomplete="off" placeholder="مثال: أحمد محمد حسن" />
                        </div>
                        <x-site.registration-field name="date_of_birth" label="تاريخ الميلاد" type="date" required :max="now()->subDay()->format('Y-m-d')" dir="ltr" />
                        <x-site.registration-field name="educational_stage_id" label="المرحلة التعليمية المطلوبة" required>
                            <select id="educational_stage_id" name="educational_stage_id" required aria-invalid="{{ $errors->has('educational_stage_id') ? 'true' : 'false' }}" @if($errors->has('educational_stage_id')) aria-describedby="educational_stage_id-error" @endif class="block min-h-12 w-full min-w-0 rounded-lg border border-border-card bg-white px-3 py-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">
                                <option value="">اختر المرحلة التعليمية</option>
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}" @selected(old('educational_stage_id') == $stage->id)>{{ $stage->title }}</option>
                                @endforeach
                            </select>
                        </x-site.registration-field>
                    </div>
                </fieldset>
                <fieldset class="min-w-0">
                    <legend class="mb-5 text-xl font-bold text-brand-navy">بيانات ولي الأمر</legend>
                    <div class="grid min-w-0 gap-5 sm:grid-cols-2">
                        <x-site.registration-field name="guardian_name" label="اسم ولي الأمر" required maxlength="150" autocomplete="name" />
                        <x-site.registration-field name="guardian_phone" label="هاتف ولي الأمر" type="tel" required maxlength="30" autocomplete="tel" dir="ltr" placeholder="03 123 456 / +961 3 123 456" />
                        <div class="sm:col-span-2">
                            <x-site.registration-field name="guardian_email" label="البريد الإلكتروني لولي الأمر" type="email" maxlength="255" autocomplete="email" dir="ltr" />
                        </div>
                    </div>
                </fieldset>
                <fieldset class="min-w-0 space-y-5">
                    <legend class="mb-5 text-xl font-bold text-brand-navy">معلومات إضافية</legend>
                    <x-site.registration-field name="notes" label="ملاحظات">
                        <textarea id="notes" name="notes" rows="4" maxlength="3000" aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}" @if($errors->has('notes')) aria-describedby="notes-error" @endif class="block w-full min-w-0 rounded-lg border border-border-card px-3 py-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">{{ is_string(old('notes')) ? old('notes') : '' }}</textarea>
                    </x-site.registration-field>
                    <p id="document-help" class="text-sm leading-7 text-text-secondary">يمكن إرفاق شهادة نجاح الصف السابق أو مستند مطلوب للتسجيل. ملف واحد بصيغة PDF أو JPG أو JPEG أو PNG، بحد أقصى ٥ ميغابايت. عند وجود خطأ في البيانات يرجى اختيار الملف مجدداً.</p>
                    <x-site.registration-field name="document" label="مستند التسجيل" type="file" accept=".pdf,.jpg,.jpeg,.png" :aria-describedby="$errors->has('document') ? 'document-help document-error' : 'document-help'" />
                </fieldset>
                <button type="submit" class="min-h-12 w-full rounded-lg bg-brand-navy px-8 py-3 font-bold text-white transition hover:bg-brand-navy-dark sm:w-auto">إرسال طلب التسجيل</button>
            </form>
        @endif
    </section>
</x-layouts.app>
