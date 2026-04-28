<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('restaurants.php');
}

$pdo = bujairi_pdo();
$r = null;
$err = '';
try {
    $r = restaurant_by_id($pdo, $id);
} catch (Throwable $e) {
    $err = 'تعذر الاتصال بقاعدة البيانات. تحقق من بيانات `db.php`.';
}

if (!$r) {
    http_response_code(404);
    $pageTitle = 'غير موجود';
    ?><!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo h($pageTitle); ?></title>
        <link rel="stylesheet" href="https://s3.ticketmx.com/fonts/NeoSansArabic/NeoSansArabic.css">
        <script src="https://cdn.tailwindcss.com"></script>
        <script>tailwind.config = { theme: { extend: { colors: { bujairi: '#a68b5a' } } } };</script>
    </head>
    <body class="min-h-screen bg-[#f2efe9] text-[#2d2438] flex items-center justify-center px-4" style="font-family: 'Neo Sans Arabic', 'Segoe UI', sans-serif;">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow border border-gray-100 p-6 text-center">
            <div class="font-extrabold text-xl mb-2">المطعم غير موجود</div>
            <?php if ($err !== ''): ?>
                <div class="text-sm text-red-700 mb-4"><?php echo h($err); ?></div>
            <?php endif; ?>
            <a href="<?php echo h(url('restaurants.php')); ?>" class="inline-block mt-2 px-5 py-3 rounded-xl bg-[#ffc750] font-bold">رجوع للمطاعم</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = trim((string) ($r['name'] ?? 'مطعم'));
$min = (float) ($r['minimum_charge'] ?? 50);
$image = trim((string) ($r['image_url'] ?? ''));
$logo = trim((string) ($r['logo_url'] ?? ''));
$type = trim((string) ($r['type'] ?? ''));
$desc = trim((string) ($r['description'] ?? ''));
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="https://s3.ticketmx.com/fonts/NeoSansArabic/NeoSansArabic.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { bujairi: '#a68b5a' } } } };</script>
    <style>
        .time-slot {
            cursor: pointer;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        .time-slot.selected {
            background: #1e293b;
            color: #fff;
            border-color: #0f172a;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f2efe9] text-[#2d2438]" style="font-family: 'Neo Sans Arabic', 'Segoe UI', sans-serif;">
<header class="bg-white border-b border-[#e5e0d8]">
    <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-extrabold"><?php echo h($pageTitle); ?></h1>
            <a href="<?php echo h(url('restaurants.php')); ?>" class="text-sm font-bold text-[#4b3447] hover:underline">← رجوع للمطاعم</a>
        </div>
        <?php if ($type !== ''): ?>
            <p class="mt-2 text-sm text-gray-600"><?php echo h($type); ?></p>
        <?php endif; ?>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 py-10">
    <div class="grid md:grid-cols-2 gap-8 mb-10">
        <div class="rounded-2xl overflow-hidden shadow bg-white border border-gray-100">
            <?php if ($image !== ''): ?>
                <img src="<?php echo h($image); ?>" class="w-full h-full object-cover" alt="">
            <?php else: ?>
                <div class="aspect-video bg-gray-100"></div>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($logo !== ''): ?>
                <img src="<?php echo h($logo); ?>" class="h-20 mb-3 object-contain" alt="">
            <?php endif; ?>
            <div class="text-gray-700 leading-relaxed"><?php echo h($desc); ?></div>
            <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-100">
                الحد الأدنى للفاتورة: <strong><?php echo h(number_format($min, 2)); ?> ريال</strong>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 max-w-lg mx-auto">
        <h2 class="text-xl font-extrabold mb-4">تفاصيل الحجز</h2>
        <form method="post" action="<?php echo h(url('booking/create.php')); ?>" id="bookingForm">
            <input type="hidden" name="restaurantId" value="<?php echo h((string) ((int) ($r['id'] ?? 0))); ?>">
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">اختر التاريخ</label>
                <select name="BookingDate" id="dateSelect" required class="w-full p-3 border rounded-lg"></select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">عدد الضيوف</label>
                <div class="flex items-center justify-between border rounded-lg p-2">
                    <button type="button" onclick="updateQty(-1)" class="w-10 h-10 bg-gray-100 rounded-md">-</button>
                    <input name="Guests" type="number" id="guestCount" value="1" min="1" max="20" readonly class="text-center font-bold w-16 border-none">
                    <button type="button" onclick="updateQty(1)" class="w-10 h-10 bg-gray-100 rounded-md">+</button>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">اختر الوقت</label>
                <div class="grid grid-cols-3 gap-2 max-h-60 overflow-y-auto" id="timeSlotsContainer"></div>
                <input type="hidden" name="SelectedTime" id="selectedTimeInput" value="">
            </div>
            <div class="bg-amber-50 p-4 rounded-lg border border-amber-100 flex justify-between font-bold mb-4">
                <span>الإجمالي</span>
                <span>
                    <span class="text-sm text-slate-700 font-bold">(<span id="totalQty">1</span> × <?php echo h(number_format($min, 2)); ?>)</span>
                    <span class="mx-1">=</span>
                    <span id="totalPrice"><?php echo h(number_format($min, 2)); ?> ريال</span>
                </span>
            </div>
            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 rounded-xl">احجز الآن</button>
        </form>
    </div>
</main>

<script>window.BUJAIRI_MIN_CHARGE = <?php echo json_encode($min); ?>;</script>
<script>window.BUJAIRI_MAX_GUESTS = 20;</script>
<script src="<?php echo h(url('assets/js/restaurant-booking.js?v=2')); ?>"></script>
<script src="<?php echo h(url('assets/js/toast.js?v=1')); ?>"></script>
</body>
</html>

