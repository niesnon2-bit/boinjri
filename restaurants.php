<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$pageTitle = 'مطاعم البجيري';
$restaurants = [];
$err = '';

try {
    $st = bujairi_pdo()->query('SELECT * FROM restaurants ORDER BY id ASC');
    $restaurants = $st ? ($st->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
} catch (Throwable $e) {
    $err = 'تعذر الاتصال بقاعدة البيانات. تحقق من بيانات `db.php`.';
    $restaurants = [];
}
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
<body class="min-h-screen bg-[#f2efe9] text-[#2d2438]" style="font-family: 'Neo Sans Arabic', 'Segoe UI', sans-serif;">
<header class="bg-white border-b border-[#e5e0d8]">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-extrabold"><?php echo h($pageTitle); ?></h1>
            <a href="<?php echo h(url('index.php')); ?>" class="text-sm font-bold text-[#4b3447] hover:underline">الرئيسية</a>
        </div>
        <p class="mt-2 text-sm text-gray-600">اختر مطعماً للانتقال إلى صفحة تفاصيله.</p>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-10">
    <?php if ($err !== ''): ?>
        <div class="max-w-xl mx-auto rounded-2xl bg-red-50 text-red-800 border border-red-100 px-5 py-4 text-center font-bold">
            <?php echo h($err); ?>
        </div>
    <?php elseif (!$restaurants): ?>
        <div class="max-w-xl mx-auto rounded-2xl bg-white border border-gray-100 shadow px-6 py-6 text-center">
            <div class="font-extrabold mb-2">لا توجد مطاعم للعرض حالياً</div>
            <div class="text-sm text-gray-600">تأكد من جدول <code>restaurants</code> والحقول المطلوبة.</div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($restaurants as $item): ?>
                <?php
                $id = (int) ($item['id'] ?? 0);
                $name = trim((string) ($item['name'] ?? 'مطعم'));
                $type = trim((string) ($item['type'] ?? ''));
                $image = trim((string) ($item['image_url'] ?? ''));
                $logo = trim((string) ($item['logo_url'] ?? ''));
                ?>
                <a href="<?php echo h(url('restaurant.php?id=' . $id)); ?>"
                   class="block rounded-2xl overflow-hidden bg-white border border-gray-100 shadow hover:shadow-lg transition">
                    <div class="aspect-video overflow-hidden bg-gray-100">
                        <?php if ($image !== ''): ?>
                            <img src="<?php echo h($image); ?>" class="w-full h-full object-cover" alt="<?php echo h($name); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="p-4 flex gap-3 items-end">
                        <?php if ($logo !== ''): ?>
                            <img src="<?php echo h($logo); ?>" class="h-12 w-12 object-contain rounded bg-white" alt="">
                        <?php else: ?>
                            <div class="h-12 w-12 rounded bg-amber-50 border border-amber-100"></div>
                        <?php endif; ?>
                        <div>
                            <div class="font-extrabold"><?php echo h($name); ?></div>
                            <?php if ($type !== ''): ?>
                                <div class="text-sm text-gray-500"><?php echo h($type); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>

