<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$body = [];
if ($raw !== false && $raw !== '') {
    $dec = json_decode($raw, true);
    if (is_array($dec)) {
        $body = $dec;
    }
}

$id = (int) ($body['orderId'] ?? $_POST['orderId'] ?? 0);
$pathIn = trim((string) ($body['path'] ?? ''));
$labelIn = trim((string) ($body['label'] ?? ''));

if ($id < 1) {
    json_response(['ok' => false, 'message' => 'طلب غير صالح'], 400);
}

if ($pathIn === '') {
    json_response(['ok' => false, 'message' => 'path مطلوب'], 400);
}
if (strlen($pathIn) > 512 || str_contains($pathIn, '..')) {
    json_response(['ok' => false, 'message' => 'مسار غير صالح'], 400);
}
if ($pathIn[0] !== '/') {
    json_response(['ok' => false, 'message' => 'مسار غير صالح'], 400);
}
if (preg_match('#/(admin|vendor|includes|config|sql|tools)(/|$)#i', $pathIn)) {
    json_response(['ok' => false, 'message' => 'مسار غير مسموح'], 400);
}

$labelIn = mb_substr($labelIn, 0, 200, 'UTF-8');
if ($labelIn === '') {
    $labelIn = 'صفحة الحجز';
}

$pdo = bujairi_pdo();
$order = order_by_id($pdo, $id);
if (!$order) {
    json_response(['ok' => false, 'message' => 'غير موجود'], 404);
}

if (($order['status'] ?? '') !== 'Draft') {
    json_response(['ok' => false, 'message' => 'غير مسموح'], 403);
}

$now = date('Y-m-d H:i:s');
try {
    $st = $pdo->prepare(
        'UPDATE orders SET customer_presence_at = ?, customer_presence_path = ?, customer_presence_label = ? WHERE id = ? AND status = \'Draft\''
    );
    $st->execute([$now, $pathIn, $labelIn, $id]);
} catch (Throwable $e) {
    // لا نكسر رحلة العميل أو التوجيه إذا لم يتم تنفيذ migration أعمدة التتبع.
    // presence ping ميزة مساعدة فقط، وليست شرطا وظيفيا.
    json_response([
        'ok' => true,
        'presenceTracked' => false,
        'message' => 'presence tracking columns are missing'
    ]);
}

bujairi_pusher_notify_admin_customer_presence($id, [
    'seenAt' => $now,
    'path' => $pathIn,
    'label' => $labelIn,
    'active' => true,
]);

json_response(['ok' => true]);
