<x-layouts.app :site-settings="$siteSettings" :homepage-settings="$homepageSettings" :title="'تم استلام طلب التسجيل | '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد')">
    <section class="mx-auto max-w-2xl px-4 py-16 text-center md:px-8 md:py-24">
        <div class="rounded-2xl border border-border-card bg-surface-card p-6 md:p-10">
            <p class="font-bold text-text-gold">القبول والتسجيل</p>
            <h1 class="mt-4 text-3xl font-black leading-normal text-brand-navy">تم استلام طلب التسجيل بنجاح</h1>
            <p class="mt-5 leading-8 text-text-secondary">يرجى الاحتفاظ برقم المرجع للتواصل مع إدارة المدرسة بشأن الطلب. سيخضع الطلب للمراجعة، ولا يُعد تقديمه تأكيداً للقبول.</p>
            <p class="mt-7 text-sm font-bold">رقم المرجع</p>
            <p dir="ltr" class="mt-3 select-all break-all rounded-lg border border-border-card bg-white p-4 font-latin text-lg font-bold text-brand-navy md:text-xl">{{ $reference }}</p>
            <a href="{{ route('home') }}" class="mt-8 inline-flex min-h-12 items-center justify-center rounded-lg bg-brand-navy px-6 py-3 font-bold text-white hover:bg-brand-navy-dark">العودة إلى الرئيسية</a>
        </div>
    </section>
</x-layouts.app>
