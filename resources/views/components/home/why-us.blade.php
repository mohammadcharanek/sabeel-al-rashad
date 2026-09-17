@props(['siteSettings', 'features'])

<section id="why-us" class="bg-white py-16 md:py-24">
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">
        <x-home.section-title
            eyebrow="مميزاتنا"
            :title="'لماذا '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد').'؟'"
            description="تعرف على مميزات المدرسة كما تعتمدها الإدارة."
        />

        @if ($features->isNotEmpty())
            <div class="mt-8 grid grid-cols-1 gap-3 md:mt-12 md:grid-cols-2 md:gap-9 lg:grid-cols-3">
                @foreach ($features as $feature)
                    <x-home.feature-card
                        :title="$feature->title"
                        :description="$feature->description ?? ''"
                        :icon="$feature->icon ?? 'academic'"
                    />
                @endforeach
            </div>
        @else
            <p class="mx-auto mt-10 max-w-[620px] rounded-2xl border border-border-soft bg-[#F4F7FB] px-6 py-8 text-center text-sm leading-8 text-text-muted">
                ستُعرض مميزات المدرسة هنا بعد اعتماد محتواها.
            </p>
        @endif
    </div>
</section>
