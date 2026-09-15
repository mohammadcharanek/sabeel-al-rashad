@props(['siteSettings'])

<section
    id="why-us"
    class="bg-white py-16 md:py-24"
>

    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <x-home.section-title
            eyebrow="مميزاتنا"
            :title="'لماذا '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد').'؟'"
            description="بيئة تعليمية متكاملة تجمع التفوق الأكاديمي مع الشخصية السوية والقيم الراسخة"
        />

        <div
            class="mt-8 grid grid-cols-1 gap-3
                   md:mt-12 md:grid-cols-2 md:gap-9
                   lg:grid-cols-3"
        >

            {{-- Mobile #2 / Tablet+Desktop #1 --}}
            <x-home.feature-card
                title="تعليم أكاديمي متميز"
                description="مناهج دراسية متطورة ومعتمدة تضمن التفوق الأكاديمي لكل طالب."
                icon="academic"
                class="order-2 md:order-1"
            />

            {{-- Mobile #1 / Tablet+Desktop #2 --}}
            <x-home.feature-card
                title="بيئة تعليمية آمنة"
                description="بيئة مدرسية آمنة ومحفزة تتيح للطلاب النمو بثقة وأمان تام."
                icon="safe"
                class="order-1 md:order-2"
            />

            {{-- Mobile #4 / Tablet+Desktop #3 --}}
            <x-home.feature-card
                title="كادر تعليمي مؤهل"
                description="معلمون ذوو خبرة ومؤهلات عالية يُرسّخون حب المعرفة والاستكشاف."
                icon="staff"
                class="order-4 md:order-3"
            />

            {{-- Mobile #3 / Tablet+Desktop #4 --}}
            <x-home.feature-card
                title="تنمية القيم والأخلاق"
                description="ترسيخ القيم الأصيلة والأخلاق الحميدة في شخصية الطالب."
                icon="values"
                class="order-3 md:order-4"
            />

            {{-- Mobile #6 / Tablet+Desktop #5 --}}
            <x-home.feature-card
                title="أنشطة متنوعة"
                description="برامج إثرائية تنمي المواهب والمهارات الإبداعية خارج الصف."
                icon="activities"
                class="order-6 md:order-5"
            />

            {{-- Mobile #5 / Tablet+Desktop #6 --}}
            <x-home.feature-card
                title="متابعة مستمرة"
                description="تواصل دوري مع أولياء الأمور ومتابعة منتظمة لتقدم كل طالب."
                icon="followup"
                class="order-5 md:order-6"
            />

        </div>

    </div>

</section>
