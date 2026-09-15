@props([
    'siteSettings',
    'newsPosts' => collect(),
])

<section
    id="news"
    class="bg-[#F4F7FB] py-16 md:py-20 lg:py-24"
>
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <x-home.section-title
            eyebrow="آخر المستجدات"
            title="آخر الأخبار"
            :description="'تابع أحدث أخبار وفعاليات '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد')"
        />

        @if ($newsPosts->isNotEmpty())

            <div
                class="mt-10 grid gap-6
                       md:mt-12 md:grid-cols-2
                       lg:grid-cols-3"
            >

                @foreach ($newsPosts as $post)

                    <x-home.news-card
                        :title="$post->title"
                        :category="$post->category"
                        :date="$post->published_at?->locale('ar')->translatedFormat('j F Y')"
                        :excerpt="$post->excerpt"
                        :image="$post->featured_image_url"
                        @class([
                            'md:col-span-2 md:w-[calc(50%-12px)] md:justify-self-center lg:col-span-1 lg:w-auto'
                                => $loop->last && $newsPosts->count() === 3,
                        ])
                    />

                @endforeach

            </div>

        @else

            <div
                class="mx-auto mt-12 max-w-[620px]
                       rounded-2xl border border-border-soft
                       bg-white p-8 text-center"
            >
                <p class="text-[15px] text-text-muted">
                    لا توجد أخبار منشورة حالياً.
                </p>
            </div>

        @endif

    </div>
</section>
