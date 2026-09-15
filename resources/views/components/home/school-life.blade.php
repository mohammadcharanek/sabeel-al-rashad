@props(['siteSettings'])

<section
    id="school-life"
    class="bg-brand-navy py-16 md:py-24"
>

    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <x-home.section-title
            eyebrow="معرض الصور"
            :title="'الحياة في '.($siteSettings->exists ? $siteSettings->text('school_name_ar', 'سبيل الرشاد') : 'سبيل الرشاد')"
            description="اكتشف عالماً من التعلم والنشاط والإبداع في بيئتنا المدرسية المتميزة"
            :dark="true"
        />

        {{-- Mobile gallery --}}
        <div class="mt-8 space-y-3 md:hidden">

            <x-home.school-life-image
                src="images/school-life/labs.jpg"
                alt="المختبرات العلمية"
                class="h-[210px]"
            />

            <div class="grid grid-cols-2 gap-3">

                <x-home.school-life-image
                    src="images/school-life/sports.jpg"
                    alt="الأنشطة الرياضية"
                    class="h-[190px]"
                />

                <x-home.school-life-image
                    src="images/school-life/events.jpg"
                    alt="الفعاليات المدرسية"
                    class="h-[190px]"
                />

            </div>

            <x-home.school-life-image
                src="images/school-life/science.jpg"
                alt="الأنشطة العلمية"
                class="h-[190px]"
            />

            <x-home.school-life-image
                src="images/school-life/classrooms.jpg"
                alt="الصفوف الدراسية"
                class="h-[150px]"
            />

        </div>

        {{-- Tablet gallery --}}
        <div class="mt-12 hidden md:block lg:hidden">

            <x-home.school-life-image
                src="images/school-life/labs.jpg"
                alt="المختبرات العلمية"
                class="h-[250px]"
            />

            <div class="mt-3 grid grid-cols-2 gap-3">

                <x-home.school-life-image
                    src="images/school-life/sports.jpg"
                    alt="الأنشطة الرياضية"
                    class="h-[160px]"
                />

                <x-home.school-life-image
                    src="images/school-life/events.jpg"
                    alt="الفعاليات المدرسية"
                    class="h-[160px]"
                />

                <x-home.school-life-image
                    src="images/school-life/science.jpg"
                    alt="الأنشطة العلمية"
                    class="h-[150px]"
                />

                <x-home.school-life-image
                    src="images/school-life/classrooms.jpg"
                    alt="الصفوف الدراسية"
                    class="h-[150px]"
                />

            </div>

        </div>

        {{-- Desktop gallery --}}
        <div
            class="mt-12 hidden
                   grid-cols-[1fr_1fr]
                   gap-3
                   lg:grid"
        >

            {{-- Large image --}}
            <x-home.school-life-image
                src="images/school-life/labs.jpg"
                alt="المختبرات العلمية"
                class="h-[628px]"
            />

            {{-- Four smaller images --}}
            <div class="grid grid-cols-2 gap-3">

                <x-home.school-life-image
                    src="images/school-life/sports.jpg"
                    alt="الأنشطة الرياضية"
                    class="h-[306px]"
                />

                <x-home.school-life-image
                    src="images/school-life/events.jpg"
                    alt="الفعاليات المدرسية"
                    class="h-[306px]"
                />

                <x-home.school-life-image
                    src="images/school-life/science.jpg"
                    alt="الأنشطة العلمية"
                    class="h-[310px]"
                />

                <x-home.school-life-image
                    src="images/school-life/classrooms.jpg"
                    alt="الصفوف الدراسية"
                    class="h-[310px]"
                />

            </div>

        </div>

        <div class="mt-8 flex justify-center">

            <a
                href="#"
                class="inline-flex h-[47px]
                       items-center justify-center
                       rounded-full
                       border border-white/45
                       px-7
                       text-[15px] font-semibold
                       text-white
                       transition
                       hover:border-brand-gold
                       hover:bg-brand-gold
                       hover:text-brand-navy-dark"
            >
                اكتشف الحياة المدرسية
            </a>

        </div>

    </div>

</section>
