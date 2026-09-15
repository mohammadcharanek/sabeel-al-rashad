@props([
    'stages' => collect(),
])

<section
    id="stages"
    class="bg-[#F4F7FB] py-16 md:py-20 lg:py-24"
>
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <x-home.section-title
            eyebrow="برامجنا التعليمية"
            title="المراحل التعليمية"
            description="نرافق الطالب في كل مرحلة من مراحل نموه بمناهج متطورة وكادر تربوي متخصص"
        />

        @if ($stages->isNotEmpty())

            <div
                class="mt-10 grid gap-4
                       md:mt-12 md:grid-cols-2 md:gap-9
                       lg:grid-cols-3 lg:gap-[60px]"
            >
                @foreach ($stages as $stage)

                    @php
                        $number = strtr((string) $loop->iteration, [
                            '0' => '٠',
                            '1' => '١',
                            '2' => '٢',
                            '3' => '٣',
                            '4' => '٤',
                            '5' => '٥',
                            '6' => '٦',
                            '7' => '٧',
                            '8' => '٨',
                            '9' => '٩',
                        ]);

                        $specialClass = $loop->iteration === 3
                            ? 'md:col-span-2 md:w-[334px] md:justify-self-center lg:col-span-1 lg:w-auto'
                            : '';
                    @endphp

                    <x-home.stage-card
                        :number="$number"
                        :title="$stage->title"
                        :description="$stage->description"
                        :icon="$stage->icon ?: 'elementary'"
                        :href="$stage->link_url"
                        :link-label="$stage->link_label ?: 'تعرف أكثر'"
                        :class="$specialClass"
                    />

                @endforeach
            </div>

        @else

            <div
                class="mt-10 rounded-xl
                       border border-[#E5E7EB]
                       bg-white
                       px-6 py-10
                       text-center
                       md:mt-12"
            >
                <p class="text-[15px] font-medium text-text-muted">
                    لا توجد مراحل تعليمية متاحة حالياً.
                </p>
            </div>

        @endif

    </div>
</section>