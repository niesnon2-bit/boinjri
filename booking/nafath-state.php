<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();

$id = (int) ($_GET['id'] ?? 0);
$img = bujairi_nafath_fixed_banner_url();
$code = '';
if ($id > 0) {
    $order = order_by_id(bujairi_pdo(), $id);
    if ($order) {
        $code = trim((string) ($order['nafath_code'] ?? ''));
    }
}

header('Cache-Control: no-store');
json_response(['orderId' => $id, 'code' => $code, 'imageUrl' => $img]);
