@props([
    'events' => collect(),
])

<section
    id="events"
    class="bg-white py-16 md:py-20 lg:py-24"
>
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <x-home.section-title
            eyebrow="التقويم المدرسي"
            title="الفعاليات القادمة"
            description="لا تفوّت أهم الفعاليات والأنشطة المدرسية القادمة"
        />

        @if ($events->isNotEmpty())

            <div
                class="mt-10 grid gap-4
                       md:mt-12
                       lg:grid-cols-2 lg:gap-x-20 lg:gap-y-5"
            >
                @foreach ($events as $event)

                    @php
                        $day = $event->starts_at->format('j');

                        $day = strtr($day, [
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

                        $month = $event->starts_at
                            ->locale('ar')
                            ->translatedFormat('F');
                    @endphp

                    <x-home.event-row
                        :day="$day"
                        :month="$month"
                        :title="$event->title"
                        :description="$event->description"
                    />

                @endforeach
            </div>

        @else

            <div
                class="mt-10 rounded-xl
                       border border-[#E5E7EB]
                       bg-[#F7F8FA]
                       px-6 py-10
                       text-center
                       md:mt-12"
            >
                <p class="text-[15px] font-medium text-text-muted">
                    لا توجد فعاليات قادمة حالياً.
                </p>
            </div>

        @endif

    </div>
</section>