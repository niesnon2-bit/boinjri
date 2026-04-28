<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
$orderId = (int) ($_GET['orderId'] ?? 0);
$order = order_by_id(bujairi_pdo(), $orderId);
$no = '';
if ($order && !empty($order['transaction_no'])) {
    $no = (string) $order['transaction_no'];
}
json_response(['transactionNo' => $no]);
