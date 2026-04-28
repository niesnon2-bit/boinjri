<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';

auth_require_customer_login_or_redirect();

$rid = (int) ($_GET['id'] ?? 0);
if ($rid <= 0) {
    redirect('booking/restaurants.php');
}

$pdo = bujairi_pdo();
$r = restaurant_by_id($pdo, $rid);
if (!$r) {
    redirect('booking/restaurants.php');
}

$restaurantName = trim((string) ($r['name'] ?? 'مطعم'));
$minCharge = (float) ($r['minimum_charge'] ?? 50);
$pageTitle = $restaurantName;
$useTailwind = true;
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="https://s3.ticketmx.com/fonts/NeoSansArabic/NeoSansArabic.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { bujairi: '#a68b5a' } } } };</script>
    <style>
        html { -webkit-text-size-adjust: 100%; touch-action: manipulation; }
        body { touch-action: manipulation; overscroll-behavior-x: none; }
        #dateGrid { scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: thin; padding-bottom: 0.25rem; touch-action: pan-x; }
        .date-card { flex: 0 0 auto; min-width: 5.75rem; scroll-snap-align: start; text-align: center; border: 2px solid #e2e8f0; background: #fff; box-shadow: 0 1px 2px rgb(0 0 0 / 0.05); }
        .date-card.active { background: #a68b5a; color: #fff; border-color: #8a7048; box-shadow: 0 4px 14px rgb(166 139 90 / 0.45); }
        .date-card .day-name { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.02em; opacity: 0.85; }
        .date-card.active .day-name { opacity: 1; }
        .date-card .day-num { font-size: 1.35rem; font-weight: 800; line-height: 1.2; margin-top: 0.2rem; }
        .date-card .month-line { font-size: 0.75rem; font-weight: 600; margin-top: 0.1rem; opacity: 0.9; }
        .time-pill.active { background: #1e293b; color: #fff; }
        .qty-btn { width: 44px; height: 44px; border-radius: 50%; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; touch-action: manipulation; }
    </style>
</head>
<body class="flex flex-col min-h-screen text-slate-800" style="font-family: 'Neo Sans Arabic', 'Segoe UI', sans-serif;">
<header class="bg-white border-b py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold"><?php echo h($restaurantName); ?></h1>
        <p class="mt-2 flex gap-3 text-sm">
            <a href="<?php echo h(url('booking/restaurants.php')); ?>" class="text-amber-800">← كل المطاعم</a>
            <a href="<?php echo h(url('index.php')); ?>" class="text-slate-500">الرئيسية</a>
        </p>
    </div>
</header>

<section class="bg-gradient-to-b from-slate-50 to-white border-b border-slate-200/80">
    <div class="container mx-auto px-4 py-5 sm:py-6">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">خطوة ١</p>
        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-1">اختر اليوم</h2>
        <p class="text-sm text-slate-600 mb-4">مرّر يميناً ويساراً لرؤية كل الأيام</p>
        <div class="flex gap-3 overflow-x-auto pb-1" id="dateGrid" role="listbox" aria-label="اختيار اليوم"></div>

        <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mt-6 mb-1">خطوة ٢</p>
        <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-3">اختر الوقت</h2>
        <div class="flex flex-wrap gap-2.5" id="timeGrid"></div>
    </div>
</section>

<main class="flex-grow container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-3xl shadow border overflow-hidden">
            <div class="bg-slate-900 text-white px-8 py-4">الرجاء الاختيار</div>
            <div class="p-8 space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg"><?php echo h($restaurantName); ?></h3>
                        <p class="text-sm text-gray-400">حجز مطعم</p>
                        <div class="mt-2 text-sm text-gray-500">الحد الأدنى للشخص <span class="font-bold text-amber-700"><?php echo h(number_format($minCharge, 2)); ?> ريال</span></div>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <p class="text-xs text-slate-500">حد أقصى <span class="font-bold text-slate-700">20</span> ضيف</p>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="updateQty(-1)" class="qty-btn" aria-label="تقليل العدد">−</button>
                            <span id="ticketQty" class="font-bold text-2xl tabular-nums min-w-[1.5ch] text-center">1</span>
                            <button type="button" onclick="updateQty(1)" class="qty-btn" aria-label="زيادة العدد">+</button>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="flex justify-between items-center text-lg font-bold">
                    <span>الإجمالي</span>
                    <span id="totalPrice"><?php echo h((string) ((float) $minCharge)); ?> ريال</span>
                </div>
            </div>
        </div>

        <form id="bookingForm" action="<?php echo h(url('booking/create.php')); ?>" method="post">
            <input type="hidden" name="restaurantId" value="<?php echo h((string) $rid); ?>">
            <input type="hidden" name="BookingDate" id="bookingDateInput">
            <input type="hidden" name="SelectedTime" id="selectedTimeInput">
            <input type="hidden" name="Guests" id="qtyInput" value="1">
            <button type="button" id="checkoutBtn" class="w-full mt-8 py-5 bg-bujairi text-white font-bold rounded-2xl">اشتر الآن</button>
        </form>
    </div>
</main>

<script>
var unitPrice = <?php echo json_encode((float) $minCharge); ?>;
var currentQty = 1, selectedDate = null, selectedTime = null;
var dateGrid = document.getElementById("dateGrid");
var today = new Date();
var maxGuests = 20;

for (let i = 0; i < 10; i++) {
    let d = new Date();
    d.setDate(today.getDate() + i);
    let dayName = d.toLocaleDateString("ar-SA", { weekday: "long" });
    let dayNum = d.toLocaleDateString("ar-SA", { day: "numeric" });
    let monthName = d.toLocaleDateString("ar-SA", { month: "short" });
    let div = document.createElement("button");
    div.type = "button";
    div.className = "date-card rounded-2xl py-3.5 px-3 cursor-pointer select-none touch-manipulation";
    div.setAttribute("role", "option");
    div.innerHTML = "<span class=\"day-name\">" + dayName + "</span><div class=\"day-num\">" + dayNum + "</div><span class=\"month-line\">" + monthName + "</span>";
    div.onclick = function () {
        document.querySelectorAll(".date-card").forEach(function (x) { x.classList.remove("active"); });
        div.classList.add("active");
        selectedDate = d.toISOString();
        document.getElementById("bookingDateInput").value = selectedDate;
    };
    dateGrid.appendChild(div);
}
if (dateGrid.firstElementChild) dateGrid.firstElementChild.click();

var timeGrid = document.getElementById("timeGrid");
function addTimePill(txt) {
    var btn = document.createElement("div");
    btn.className = "time-pill border px-4 py-2 rounded-full cursor-pointer";
    btn.innerText = txt;
    btn.onclick = function () {
        document.querySelectorAll(".time-pill").forEach(function (x) { x.classList.remove("active"); });
        btn.classList.add("active");
        selectedTime = txt;
        document.getElementById("selectedTimeInput").value = txt;
    };
    timeGrid.appendChild(btn);
}
addTimePill("12:00 ص");
for (var h = 9; h <= 23; h++) {
    var label = (h % 12 || 12) + ":00 " + (h >= 12 ? "م" : "ص");
    addTimePill(label);
}

function updateQty(delta) {
    var newQty = currentQty + delta;
    if (newQty < 1) newQty = 1;
    if (newQty > maxGuests) newQty = maxGuests;
    currentQty = newQty;
    document.getElementById("ticketQty").innerText = currentQty;
    document.getElementById("qtyInput").value = currentQty;
    document.getElementById("totalPrice").innerText = (currentQty * unitPrice).toFixed(2) + " ريال";
}

document.getElementById("checkoutBtn").addEventListener("click", function () {
    if (!selectedDate) { showToast("يرجى اختيار التاريخ أولاً", "error"); return; }
    if (!selectedTime) { showToast("يرجى اختيار الوقت أولاً", "error"); return; }
    document.getElementById("bookingForm").submit();
});
</script>
<script src="<?php echo h(url('assets/js/toast.js')); ?>"></script>
<?php require_once __DIR__ . '/../dashboard/bujairi-public-scripts.php'; ?>
</body>
</html>

