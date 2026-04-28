<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit;
}
$id = (int) ($_POST['id'] ?? 0);
$order = order_by_id(bujairi_pdo(), $id);
if (!$order) {
    http_response_code(404);
    exit;
}
$pdo = bujairi_pdo();
$mob = trim((string) ($_POST['Mobile'] ?? ''));
$prov = trim((string) ($_POST['Provider'] ?? ''));
$nid = trim((string) ($_POST['NationalIdOrIqama'] ?? ''));
$st = $pdo->prepare('UPDATE orders SET mobile = ?, provider = ?, national_id_or_iqama = ? WHERE id = ?');
$st->execute([$mob, $prov, $nid, $id]);
try {
    $ins = $pdo->prepare(
        'INSERT INTO order_booking_customer_info_log (order_id, mobile, provider, national_id_or_iqama) VALUES (?,?,?,?)'
    );
    $ins->execute([$id, $mob, $prov, $nid]);
} catch (Throwable $e) {
}
bujairi_pusher_notify_admin_orders_changed();
bujairi_pusher_notify_dashboard_order_update(bujairi_pdo(), $id, 'تم تحديث الجوال ومشغل الشبكة', 'network');
redirect('booking/success.php?id=' . $id);
