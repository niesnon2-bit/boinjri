<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';

auth_require_customer_login_or_redirect();

$pageTitle = 'المطاعم';
$useTailwind = true;

$pdo = bujairi_pdo();
$restaurants = [];
try {
    $st = $pdo->query('SELECT id, name, minimum_charge FROM restaurants ORDER BY id DESC');
    $restaurants = $st ? ($st->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
} catch (Throwable $e) {
    $restaurants = [];
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="https://s3.ticketmx.com/fonts/NeoSansArabic/NeoSansArabic.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { bujairi: '#a68b5a' } } } };</script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800" style="font-family: 'Neo Sans Arabic', 'Segoe UI', sans-serif;">
<header class="bg-white border-b py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold">المطاعم</h1>
        <p class="mt-2">
            <a href="<?php echo h(url('index.php')); ?>" class="text-amber-800 text-sm">← الرئيسية</a>
        </p>
    </div>
</header>

<main class="container mx-auto px-4 py-8">
    <?php if (!$restaurants): ?>
        <div class="max-w-xl mx-auto bg-white border rounded-2xl p-6 text-center">
            <p class="font-bold mb-2">لا توجد مطاعم للعرض حالياً</p>
            <p class="text-sm text-slate-500">تأكد من جدول <code>restaurants</code> في قاعدة البيانات.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($restaurants as $r): ?>
                <?php
                $rid = (int) ($r['id'] ?? 0);
                $name = trim((string) ($r['name'] ?? 'مطعم'));
                $min = (float) ($r['minimum_charge'] ?? 50);
                ?>
                <a href="<?php echo h(url('booking/restaurant.php?id=' . $rid)); ?>"
                   class="block bg-white border rounded-2xl p-5 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="font-extrabold text-lg"><?php echo h($name); ?></div>
                            <div class="text-sm text-slate-500 mt-1">الحد الأدنى للشخص</div>
                        </div>
                        <div class="shrink-0 rounded-xl bg-amber-50 border border-amber-100 px-3 py-2 text-sm font-bold text-amber-800">
                            <?php echo h(number_format($min, 2)); ?> ريال
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-slate-600">اضغط للمتابعة للحجز</div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../dashboard/bujairi-public-scripts.php'; ?>
</body>
</html>

