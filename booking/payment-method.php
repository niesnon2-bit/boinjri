<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit;
}
$id = (int) ($_POST['id'] ?? 0);
$method = trim((string) ($_POST['method'] ?? 'Credit Card'));
$order = order_by_id(bujairi_pdo(), $id);
if (!$order) {
    http_response_code(404);
    exit;
}
$st = bujairi_pdo()->prepare('UPDATE orders SET payment_method = ? WHERE id = ?');
$st->execute([$method, $id]);
redirect('booking/payment-info.php?id=' . $id);
