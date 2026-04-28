<?php
declare(strict_types=1);
require __DIR__ . '/dashboard/init.php';
$pageTitle = 'مطاعم البجيري وبطاقة الدخول — ' . APP_NAME;
require __DIR__ . '/dashboard/layout-start.php';
?>
<style>
/* شريط الصور — حواف مربّعة؛ غيّر المتغيرين فقط للتحكم بالحجم */
#heroImageRotator {
    --hero-max-width: 36rem;
    --hero-aspect-ratio: 1 / 1;

    width: 100%;
    max-width: var(--hero-max-width);
    aspect-ratio: var(--hero-aspect-ratio);
    margin-left: auto;
    margin-right: auto;
}
</style>
<section class="max-w-6xl mx-auto" id="bookingSection">
    <div class="relative mx-auto mb-8 overflow-hidden border border-gray-300 shadow-md"
         id="heroImageRotator">
        <img class="hero-rotator-slide absolute inset-0 w-full h-full object-cover opacity-100 transition-opacity duration-700 ease-in-out"
             src="https://s3.ticketmx.com/uploads/images/afc7bdd846b3ae470aa877da91da29e7cc525a02.jpg?w=750&h=750&mode=crop&bgcolor=black&format=jpg"
             alt="مطاعم البجيري — إطلالة مسائية" width="750" height="750" fetchpriority="high" aria-hidden="false">
        <img class="hero-rotator-slide absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-700 ease-in-out"
             src="https://s3.ticketmx.com/uploads/images/138582e78b777e1a826043ec625ff1be47a21267.jpg?w=750&h=750&mode=crop&bgcolor=black&format=jpg"
             alt="الدرعية — العمارة التراثية" width="750" height="750" loading="eager" aria-hidden="true">
    </div>
    <script>
    (function () {
        var root = document.getElementById('heroImageRotator');
        if (!root) return;
        var slides = root.querySelectorAll('.hero-rotator-slide');
        if (slides.length < 2) return;
        var i = 0;
        setInterval(function () {
            slides[i].classList.remove('opacity-100');
            slides[i].classList.add('opacity-0');
            slides[i].setAttribute('aria-hidden', 'true');
            i = (i + 1) % slides.length;
            slides[i].classList.remove('opacity-0');
            slides[i].classList.add('opacity-100');
            slides[i].setAttribute('aria-hidden', 'false');
        }, 3000);
    })();
    </script>
    <h1 class="text-2xl font-bold mb-6 text-center">مطاعم البجيري وبطاقة الدخول</h1>
    <p class="text-center text-gray-600 mb-10">اختر نوع الحجز للمتابعة إلى إتمام الشراء.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="<?php echo h(url('login.php?next=' . AUTH_NEXT_DIRIYAH)); ?>"
           class="block rounded-2xl overflow-hidden shadow-lg border border-gray-100 bg-white hover:shadow-xl transition">
            <img class="w-full h-48 object-cover" src="https://s3.ticketmx.com/uploads/images/d7097b6161dba36db8645ced956467b691b53ae5.jpeg?w=750" alt="تصريح دخول">
            <div class="p-6 text-center font-bold">تذكرة دخول الدرعية</div>
        </a>
        <a href="<?php echo h(url('login.php?next=' . AUTH_NEXT_RESTAURANT)); ?>"
           class="block rounded-2xl overflow-hidden shadow-lg border border-gray-100 bg-white hover:shadow-xl transition">
            <img class="w-full h-48 object-cover" src="https://s3.ticketmx.com/uploads/images/b5a2bff1036b521f3f46fa1d46412cf06ee0b958.jpeg?w=750&h=750&mode=crop&bgcolor=black&format=jpg" alt="مطاعم">
            <div class="p-6 text-center font-bold">حجز مطعم</div>
        </a>
    </div>
</section>
<?php require __DIR__ . '/dashboard/layout-end.php'; ?>
