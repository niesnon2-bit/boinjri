<?php
declare(strict_types=1);
require __DIR__ . '/dashboard/init.php';

auth_login_next_apply_get();

$error = '';
$pageTitle = 'تسجيل حساب جديد';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password_confirm'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'يرجى تعبئة البريد وكلمة المرور.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'صيغة البريد غير صالحة.';
    } elseif (mb_strlen($password) < 8) {
        $error = 'كلمة المرور يجب أن لا تقل عن 8 أحرف.';
    } elseif ($password !== $password2) {
        $error = 'تأكيد كلمة المرور غير مطابق.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $st = bujairi_pdo()->prepare(
                'INSERT INTO users (email, full_name, password_hash) VALUES (?, ?, ?)'
            );
            $st->execute([$email, $fullName !== '' ? $fullName : null, $hash]);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $error = 'هذا البريد مسجّل مسبقاً.';
            } else {
                $error = 'تعذر إنشاء الحساب. حاول لاحقاً.';
            }
        }
        if ($error === '') {
            $id = (int) bujairi_pdo()->lastInsertId();
            if ($id > 0 && \function_exists('bujairi_pusher_notify_dashboard_new_client')) {
                bujairi_pusher_notify_dashboard_new_client($id, 'تسجيل حساب جديد');
            }
            auth_login_user($id, $email);
            auth_touch_user_login($id);
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

$authFlowTitle = 'تسجيل حساب جديد';
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
        <div class="bg-[#4b3447] text-white text-center py-3 font-bold text-sm">إنشاء حساب</div>
        <form method="post" action="<?php echo h(url('register.php')); ?>" class="p-6 sm:p-8 space-y-5">
            <?php if ($returnField !== ''): ?>
                <input type="hidden" name="return" value="<?php echo h($returnField); ?>">
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="rounded-xl bg-red-50 text-red-800 border border-red-100 px-4 py-3 text-sm text-center"><?php echo h($error); ?></div>
            <?php endif; ?>
            <div>
                <label for="full_name" class="block text-sm font-bold text-[#4b3447] mb-2">الاسم الكامل</label>
                <input id="full_name" name="full_name" type="text" value="<?php echo h((string) ($_POST['full_name'] ?? '')); ?>"
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#ffc750]/80 focus:border-[#4b3447]"
                       placeholder="اختياري" autocomplete="name">
            </div>
            <div>
                <label for="email" class="block text-sm font-bold text-[#4b3447] mb-2">البريد الإلكتروني <span class="text-red-600">*</span></label>
                <input id="email" name="email" type="email" required value="<?php echo h((string) ($_POST['email'] ?? '')); ?>"
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#ffc750]/80 focus:border-[#4b3447]"
                       autocomplete="username">
            </div>
            <div>
                <label for="password" class="block text-sm font-bold text-[#4b3447] mb-2">كلمة المرور <span class="text-red-600">*</span></label>
                <input id="password" name="password" type="password" required minlength="8"
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#ffc750]/80 focus:border-[#4b3447]"
                       autocomplete="new-password">
            </div>
            <div>
                <label for="password_confirm" class="block text-sm font-bold text-[#4b3447] mb-2">تأكيد كلمة المرور <span class="text-red-600">*</span></label>
                <input id="password_confirm" name="password_confirm" type="password" required
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#ffc750]/80 focus:border-[#4b3447]"
                       autocomplete="new-password">
            </div>
            <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-[#4b3447] focus:ring-[#ffc750]">
                تذكرني
            </label>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ffc750] hover:bg-[#f5bd3f] text-[#2d2438] font-bold text-base shadow-sm transition border border-[#e8b73a]">
                إنشاء الحساب
            </button>
        </form>
        <div class="border-t border-gray-100 px-6 py-5 text-center text-sm text-gray-600">
            لديك حساب بالفعل؟
            <a href="<?php echo h(url('login.php' . $nextQuery)); ?>" class="font-bold text-[#4b3447] hover:underline">تسجيل الدخول</a>
        </div>
    </div>
    <p class="mt-8 text-center text-sm text-gray-500">
        <a href="<?php echo h(url('index.php')); ?>" class="text-[#4b3447] font-semibold hover:underline">← العودة للرئيسية</a>
    </p>
</main>
<?php require_once __DIR__ . '/dashboard/bujairi-public-scripts.php'; ?>
</body>
</html>
